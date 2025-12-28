<?php
/**
 * GST Report Model
 * Handles GST compliance, e-invoicing, and tax reports
 */

require_once __DIR__ . '/BaseModel.php';

class GSTReport extends BaseModel {
    
    /**
     * Get GSTR-1 report (Outward supplies)
     */
    public function getGSTR1Report($startDate, $endDate) {
        $sql = "SELECT 
                o.id,
                o.order_number,
                o.order_date,
                o.customer_name,
                o.customer_email,
                o.customer_mobile,
                o.billing_address,
                o.billing_gstin,
                o.subtotal,
                o.tax_amount,
                o.total,
                o.payment_method,
                o.order_status
                FROM pos_orders o
                WHERE o.order_date BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'processing')
                ORDER BY o.order_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $orders = $stmt->fetchAll();
        
        $b2bInvoices = [];
        $b2cLargeInvoices = [];
        $b2cSmallInvoices = [];
        
        foreach ($orders as $order) {
            $invoice = [
                'invoice_number' => $order['order_number'],
                'invoice_date' => date('d-M-Y', strtotime($order['order_date'])),
                'invoice_value' => $order['total'],
                'taxable_value' => $order['subtotal'],
                'igst' => 0,
                'cgst' => $order['tax_amount'] / 2,
                'sgst' => $order['tax_amount'] / 2,
                'cess' => 0
            ];
            
            if (!empty($order['billing_gstin'])) {
                $b2bInvoices[] = array_merge($invoice, [
                    'gstin' => $order['billing_gstin'],
                    'customer_name' => $order['customer_name']
                ]);
            } elseif ($order['total'] >= 250000) {
                $b2cLargeInvoices[] = $invoice;
            } else {
                $b2cSmallInvoices[] = $invoice;
            }
        }
        
        return [
            'b2b' => $b2bInvoices,
            'b2c_large' => $b2cLargeInvoices,
            'b2c_small' => $b2cSmallInvoices,
            'summary' => [
                'b2b_count' => count($b2bInvoices),
                'b2c_large_count' => count($b2cLargeInvoices),
                'b2c_small_count' => count($b2cSmallInvoices),
                'total_taxable_value' => array_sum(array_column($orders, 'subtotal')),
                'total_tax' => array_sum(array_column($orders, 'tax_amount')),
                'total_invoice_value' => array_sum(array_column($orders, 'total'))
            ]
        ];
    }
    
    /**
     * Get HSN-wise summary
     */
    public function getHSNSummary($startDate, $endDate) {
        $sql = "SELECT 
                oi.hsn_code,
                oi.tax_rate,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.total - oi.tax_amount) as taxable_value,
                SUM(oi.tax_amount) as tax_amount
                FROM pos_order_items oi
                INNER JOIN pos_orders o ON oi.order_id = o.id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'processing')
                GROUP BY oi.hsn_code, oi.tax_rate
                ORDER BY taxable_value DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate e-invoice JSON (simplified IRN format)
     */
    public function generateEInvoice($orderId) {
        $sql = "SELECT * FROM pos_orders WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            return null;
        }
        
        $itemsSql = "SELECT * FROM pos_order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($itemsSql);
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        
        $storeSql = "SELECT * FROM pos_stores WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($storeSql);
        $stmt->execute([1]);
        $store = $stmt->fetch();
        
        $lineItems = [];
        foreach ($items as $index => $item) {
            $lineItems[] = [
                'SlNo' => ($index + 1),
                'PrdDesc' => $item['product_name'],
                'IsServc' => 'N',
                'HsnCd' => $item['hsn_code'] ?? '00000',
                'Qty' => $item['quantity'],
                'Unit' => 'PCS',
                'UnitPrice' => $item['price'],
                'TotAmt' => $item['total'],
                'Discount' => $item['discount_amount'],
                'AssAmt' => $item['total'] - $item['tax_amount'],
                'GstRt' => $item['tax_rate'],
                'IgstAmt' => 0,
                'CgstAmt' => $item['tax_amount'] / 2,
                'SgstAmt' => $item['tax_amount'] / 2,
                'TotItemVal' => $item['total']
            ];
        }
        
        $eInvoice = [
            'Version' => '1.1',
            'TranDtls' => [
                'TaxSch' => 'GST',
                'SupTyp' => 'B2C',
                'RegRev' => 'N',
                'IgstOnIntra' => 'N'
            ],
            'DocDtls' => [
                'Typ' => 'INV',
                'No' => $order['order_number'],
                'Dt' => date('d/m/Y', strtotime($order['order_date']))
            ],
            'SellerDtls' => [
                'Gstin' => $store['gst_number'] ?? '',
                'LglNm' => $store['name'] ?? 'Store',
                'Addr1' => $store['address'] ?? '',
                'Loc' => $store['city'] ?? '',
                'Pin' => $store['pincode'] ?? '',
                'Stcd' => substr($store['gst_number'] ?? '00', 0, 2)
            ],
            'BuyerDtls' => [
                'Gstin' => $order['billing_gstin'] ?? '',
                'LglNm' => $order['customer_name'] ?: 'Cash Customer',
                'Pos' => substr($store['gst_number'] ?? '00', 0, 2),
                'Addr1' => $order['billing_address'] ?: '',
                'Stcd' => substr($order['billing_gstin'] ?? '00', 0, 2)
            ],
            'ItemList' => $lineItems,
            'ValDtls' => [
                'AssVal' => $order['subtotal'],
                'CgstVal' => $order['tax_amount'] / 2,
                'SgstVal' => $order['tax_amount'] / 2,
                'IgstVal' => 0,
                'Discount' => $order['discount_amount'],
                'TotInvVal' => $order['total']
            ]
        ];
        
        return $eInvoice;
    }
    
    /**
     * Get tax summary by rate
     */
    public function getTaxSummaryByRate($startDate, $endDate) {
        $sql = "SELECT 
                oi.tax_rate,
                COUNT(DISTINCT o.id) as order_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.total - oi.tax_amount) as taxable_value,
                SUM(oi.tax_amount) as tax_collected,
                SUM(oi.total) as total_value
                FROM pos_order_items oi
                INNER JOIN pos_orders o ON oi.order_id = o.id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'processing')
                GROUP BY oi.tax_rate
                ORDER BY oi.tax_rate ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get monthly GST summary
     */
    public function getMonthlyGSTSummary($year, $month) {
        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        
        $sql = "SELECT 
                COUNT(*) as total_invoices,
                SUM(subtotal) as total_taxable_value,
                SUM(tax_amount) as total_gst,
                SUM(total) as total_invoice_value,
                AVG(tax_amount) as avg_gst_per_invoice
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $summary = $stmt->fetch();
        
        $summary['cgst'] = $summary['total_gst'] / 2;
        $summary['sgst'] = $summary['total_gst'] / 2;
        $summary['igst'] = 0;
        
        return $summary;
    }
    
    /**
     * Export GSTR-1 to JSON
     */
    public function exportGSTR1ToJSON($startDate, $endDate) {
        $report = $this->getGSTR1Report($startDate, $endDate);
        $hsnSummary = $this->getHSNSummary($startDate, $endDate);
        
        $export = [
            'gstin' => '',
            'fp' => date('mY', strtotime($startDate)),
            'b2b' => $report['b2b'],
            'b2cl' => $report['b2c_large'],
            'b2cs' => [
                [
                    'pos' => '00',
                    'txval' => $report['summary']['total_taxable_value'],
                    'iamt' => 0,
                    'camt' => $report['summary']['total_tax'] / 2,
                    'samt' => $report['summary']['total_tax'] / 2
                ]
            ],
            'hsn' => [
                'data' => $hsnSummary
            ]
        ];
        
        return json_encode($export, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get tax liability
     */
    public function getTaxLiability($startDate, $endDate) {
        $sql = "SELECT 
                SUM(tax_amount) as output_tax,
                0 as input_tax,
                SUM(tax_amount) as net_tax_liability
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch();
    }
}
