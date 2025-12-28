/**
 * Offline Mode with Synchronization
 * Handles offline cart management and automatic sync when online
 */

const OfflineMode = {
    dbName: 'BPlusPOS',
    dbVersion: 1,
    db: null,
    syncQueue: [],
    isOnline: navigator.onLine,
    
    /**
     * Initialize offline mode
     */
    init() {
        this.openDB();
        this.setupEventListeners();
        this.checkOnlineStatus();
        this.updateUI();
    },
    
    /**
     * Open IndexedDB
     */
    openDB() {
        const request = indexedDB.open(this.dbName, this.dbVersion);
        
        request.onerror = () => {
            console.error('Failed to open IndexedDB');
        };
        
        request.onsuccess = (event) => {
            this.db = event.target.result;
            console.log('IndexedDB opened successfully');
            this.loadSyncQueue();
        };
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            
            if (!db.objectStoreNames.contains('products')) {
                db.createObjectStore('products', { keyPath: 'id' });
            }
            
            if (!db.objectStoreNames.contains('cart')) {
                db.createObjectStore('cart', { keyPath: 'id' });
            }
            
            if (!db.objectStoreNames.contains('syncQueue')) {
                const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
                syncStore.createIndex('timestamp', 'timestamp', { unique: false });
            }
            
            if (!db.objectStoreNames.contains('orders')) {
                db.createObjectStore('orders', { keyPath: 'id', autoIncrement: true });
            }
        };
    },
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.updateUI();
            this.syncData();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.updateUI();
        });
    },
    
    /**
     * Check online status
     */
    checkOnlineStatus() {
        setInterval(() => {
            const wasOnline = this.isOnline;
            this.isOnline = navigator.onLine;
            
            if (!wasOnline && this.isOnline) {
                console.log('Connection restored, syncing...');
                this.syncData();
            }
        }, 5000);
    },
    
    /**
     * Update UI based on online status
     */
    updateUI() {
        const indicator = document.getElementById('offline-indicator');
        
        if (!indicator) {
            const div = document.createElement('div');
            div.id = 'offline-indicator';
            div.style.cssText = 'position: fixed; top: 70px; right: 20px; padding: 10px 20px; border-radius: 8px; z-index: 9999; display: none;';
            document.body.appendChild(div);
        }
        
        const offlineIndicator = document.getElementById('offline-indicator');
        
        if (this.isOnline) {
            offlineIndicator.style.display = 'none';
        } else {
            offlineIndicator.style.display = 'block';
            offlineIndicator.style.background = '#ff9800';
            offlineIndicator.style.color = 'white';
            offlineIndicator.innerHTML = '<i class="fas fa-wifi-slash"></i> Working Offline - Changes will sync automatically';
        }
        
        if (this.syncQueue.length > 0) {
            const syncBtn = document.getElementById('manual-sync-btn');
            if (syncBtn) {
                syncBtn.style.display = 'block';
                syncBtn.innerHTML = `<i class="fas fa-sync"></i> Sync (${this.syncQueue.length})`;
            }
        }
    },
    
    /**
     * Save cart to IndexedDB
     */
    saveCart(cart) {
        if (!this.db) return;
        
        const transaction = this.db.transaction(['cart'], 'readwrite');
        const store = transaction.objectStore('cart');
        
        store.clear();
        
        Object.values(cart).forEach(item => {
            store.put(item);
        });
    },
    
    /**
     * Load cart from IndexedDB
     */
    async loadCart() {
        if (!this.db) return {};
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['cart'], 'readonly');
            const store = transaction.objectStore('cart');
            const request = store.getAll();
            
            request.onsuccess = () => {
                const cart = {};
                request.result.forEach(item => {
                    cart[item.id] = item;
                });
                resolve(cart);
            };
            
            request.onerror = () => reject(request.error);
        });
    },
    
    /**
     * Cache products for offline access
     */
    cacheProducts(products) {
        if (!this.db) return;
        
        const transaction = this.db.transaction(['products'], 'readwrite');
        const store = transaction.objectStore('products');
        
        products.forEach(product => {
            store.put(product);
        });
    },
    
    /**
     * Get cached products
     */
    async getCachedProducts() {
        if (!this.db) return [];
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['products'], 'readonly');
            const store = transaction.objectStore('products');
            const request = store.getAll();
            
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },
    
    /**
     * Add to sync queue
     */
    addToSyncQueue(action, data) {
        const queueItem = {
            action: action,
            data: data,
            timestamp: Date.now()
        };
        
        if (!this.db) {
            this.syncQueue.push(queueItem);
            return;
        }
        
        const transaction = this.db.transaction(['syncQueue'], 'readwrite');
        const store = transaction.objectStore('syncQueue');
        store.add(queueItem);
        
        this.syncQueue.push(queueItem);
        this.updateUI();
    },
    
    /**
     * Load sync queue from IndexedDB
     */
    loadSyncQueue() {
        if (!this.db) return;
        
        const transaction = this.db.transaction(['syncQueue'], 'readonly');
        const store = transaction.objectStore('syncQueue');
        const request = store.getAll();
        
        request.onsuccess = () => {
            this.syncQueue = request.result;
            this.updateUI();
            
            if (this.isOnline && this.syncQueue.length > 0) {
                this.syncData();
            }
        };
    },
    
    /**
     * Sync data with server
     */
    async syncData() {
        if (!this.isOnline || this.syncQueue.length === 0) return;
        
        console.log('Syncing', this.syncQueue.length, 'items...');
        
        const indicator = document.getElementById('offline-indicator');
        if (indicator) {
            indicator.style.display = 'block';
            indicator.style.background = '#2196f3';
            indicator.style.color = 'white';
            indicator.innerHTML = '<i class="fas fa-sync fa-spin"></i> Syncing...';
        }
        
        const successfulSyncs = [];
        
        for (const item of this.syncQueue) {
            try {
                await this.syncItem(item);
                successfulSyncs.push(item.id);
            } catch (error) {
                console.error('Sync failed for item:', item, error);
            }
        }
        
        this.removeSyncedItems(successfulSyncs);
        
        if (indicator) {
            indicator.style.background = '#4caf50';
            indicator.innerHTML = '<i class="fas fa-check"></i> Synced successfully!';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 3000);
        }
    },
    
    /**
     * Sync individual item
     */
    async syncItem(item) {
        switch (item.action) {
            case 'process_payment':
                return await this.syncPayment(item.data);
            
            case 'update_cart':
                return true;
            
            default:
                return true;
        }
    },
    
    /**
     * Sync payment
     */
    async syncPayment(data) {
        const csrfToken = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!data.csrf_token && csrfToken) {
            data.csrf_token = csrfToken;
        }
        
        const response = await fetch('/pos/process-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Payment sync failed');
        }
        
        return await response.json();
    },
    
    /**
     * Remove synced items from queue
     */
    removeSyncedItems(itemIds) {
        if (!this.db || itemIds.length === 0) return;
        
        const transaction = this.db.transaction(['syncQueue'], 'readwrite');
        const store = transaction.objectStore('syncQueue');
        
        itemIds.forEach(id => {
            store.delete(id);
        });
        
        this.syncQueue = this.syncQueue.filter(item => !itemIds.includes(item.id));
        this.updateUI();
    },
    
    /**
     * Manual sync trigger
     */
    manualSync() {
        if (this.isOnline) {
            this.syncData();
        } else {
            alert('Cannot sync while offline. Please check your internet connection.');
        }
    }
};

if (typeof module !== 'undefined' && module.exports) {
    module.exports = OfflineMode;
}
