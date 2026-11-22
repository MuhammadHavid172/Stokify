@props(['role' => 'admin'])

@php
    $userId = auth()->id() ?? 0;
    $kiosId = auth()->user()?->kios_id;
    $initialFeed = $notifikasiFeed ?? ($kiosId ? \App\Services\NotifikasiService::getFeed($kiosId, $role, 15) : []);
@endphp

<div class="relative" 
     x-data="{ 
        notifOpen: false, 
        feed: {{ json_encode($initialFeed) }},
        readIds: [],
        userId: {{ (int) $userId }},
        get storageKey() {
            return 'stokify_read_notifs_' + this.userId;
        },
        init() {
            try {
                const stored = localStorage.getItem(this.storageKey);
                this.readIds = stored ? JSON.parse(stored) : [];
                if (!Array.isArray(this.readIds)) {
                    this.readIds = [];
                }
            } catch (e) {
                this.readIds = [];
            }
        },
        get unreadCount() {
            return this.feed.filter(item => !this.isRead(item.id)).length;
        },
        isRead(id) {
            return this.readIds.includes(String(id));
        },
        markAsRead(id) {
            const strId = String(id);
            if (!this.readIds.includes(strId)) {
                this.readIds.push(strId);
                // Batasi riwayat readIds agar localStorage tetap ramping (maksimal 100 id)
                if (this.readIds.length > 100) {
                    this.readIds = this.readIds.slice(-100);
                }
                try {
                    localStorage.setItem(this.storageKey, JSON.stringify(this.readIds));
                } catch (e) {}
            }
        },
        markAllAsRead() {
            this.feed.forEach(item => {
                const strId = String(item.id);
                if (!this.readIds.includes(strId)) {
                    this.readIds.push(strId);
                }
            });
            if (this.readIds.length > 100) {
                this.readIds = this.readIds.slice(-100);
            }
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.readIds));
            } catch (e) {}
        },
        handleItemClick(item) {
            this.markAsRead(item.id);
            if (item.url) {
                window.location.href = item.url;
            }
        },
        getItemClass(item) {
            // Ketika SUDAH dibaca: warnanya hilang (netral putih)
            if (this.isRead(item.id)) {
                return 'bg-white hover:bg-gray-50/90 border-l-4 border-l-transparent text-gray-700';
            }
            // Ketika BELUM dibaca: ada warna latar & border sesuai kondisi notifikasinya
            switch (item.color) {
                case 'rose':
                    return 'bg-rose-50/90 hover:bg-rose-100/70 border-l-4 border-l-rose-500 text-rose-950';
                case 'blue':
                    return 'bg-blue-50/90 hover:bg-blue-100/70 border-l-4 border-l-blue-500 text-blue-950';
                case 'purple':
                    return 'bg-purple-50/90 hover:bg-purple-100/70 border-l-4 border-l-purple-500 text-purple-950';
                case 'emerald':
                    return 'bg-emerald-50/90 hover:bg-emerald-100/70 border-l-4 border-l-emerald-500 text-emerald-950';
                case 'amber':
                    return 'bg-amber-50/90 hover:bg-amber-100/70 border-l-4 border-l-amber-500 text-amber-950';
                case 'teal':
                    return 'bg-teal-50/90 hover:bg-teal-100/70 border-l-4 border-l-teal-500 text-teal-950';
                default:
                    return 'bg-emerald-50/90 hover:bg-emerald-100/70 border-l-4 border-l-emerald-500 text-emerald-950';
            }
        },
        getBadgeClass(item) {
            if (this.isRead(item.id)) {
                return 'bg-gray-100 text-gray-500 border border-gray-200';
            }
            switch (item.color) {
                case 'rose':
                    return 'bg-rose-100 text-rose-800 border border-rose-200 font-bold';
                case 'blue':
                    return 'bg-blue-100 text-blue-800 border border-blue-200 font-bold';
                case 'purple':
                    return 'bg-purple-100 text-purple-800 border border-purple-200 font-bold';
                case 'emerald':
                    return 'bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold';
                case 'amber':
                    return 'bg-amber-100 text-amber-800 border border-amber-200 font-bold';
                case 'teal':
                    return 'bg-teal-100 text-teal-800 border border-teal-200 font-bold';
                default:
                    return 'bg-gray-100 text-gray-800 border border-gray-200 font-bold';
            }
        },
        getIconBgClass(item) {
            if (this.isRead(item.id)) {
                return 'bg-gray-100 text-gray-400';
            }
            switch (item.color) {
                case 'rose':
                    return 'bg-rose-200 text-rose-700';
                case 'blue':
                    return 'bg-blue-200 text-blue-700';
                case 'purple':
                    return 'bg-purple-200 text-purple-700';
                case 'emerald':
                    return 'bg-emerald-200 text-emerald-700';
                case 'amber':
                    return 'bg-amber-200 text-amber-700';
                case 'teal':
                    return 'bg-teal-200 text-teal-700';
                default:
                    return 'bg-emerald-200 text-emerald-700';
            }
        },
        getDotClass(item) {
            switch (item.color) {
                case 'rose': return 'bg-rose-500';
                case 'blue': return 'bg-blue-500';
                case 'purple': return 'bg-purple-500';
                case 'emerald': return 'bg-emerald-500';
                case 'amber': return 'bg-amber-500';
                case 'teal': return 'bg-teal-500';
                default: return 'bg-emerald-500';
            }
        },
        async refreshNotif() {
            try {
                const res = await fetch('{{ route('notifikasi.stok') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.feed && Array.isArray(data.feed)) {
                        this.feed = data.feed;
                    }
                }
            } catch (e) {}
        }
     }"
     @notif-refresh.window="refreshNotif()"
     @click.outside="notifOpen = false">

    <!-- Tombol Ikon Lonceng -->
    <button type="button" 
            @click="notifOpen = !notifOpen" 
            class="relative p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-[#166534]"
            title="Notifikasi Masuk & Peringatan Stok"
            aria-label="Notifikasi Masuk & Peringatan Stok">
        
        <svg class="w-5 h-5 text-gray-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Badge Jumlah Notifikasi Belum Dibaca -->
        <template x-if="unreadCount > 0">
            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-xs animate-pulse font-mono tabular-nums"
                  x-text="unreadCount > 99 ? '99+' : unreadCount">
            </span>
        </template>
    </button>

    <!-- Dropdown Panel Notifikasi Masuk (Single Unified Feed 15 FIFO) -->
    <div x-show="notifOpen" 
         x-cloak 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
         class="absolute right-0 mt-2 w-84 sm:w-96 bg-white border border-gray-200 rounded-2xl shadow-2xl py-2 z-50 overflow-hidden font-sans">
        
        <!-- Header Dropdown -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/90">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :class="unreadCount > 0 ? 'bg-rose-500 animate-ping' : 'bg-emerald-500'"></span>
                <div>
                    <h3 class="text-xs font-bold text-gray-900 leading-tight">Peringatan Stok & Notifikasi</h3>
                    <p class="text-[10px] text-gray-500 font-medium">15 riwayat terbaru tersimpan</p>
                </div>
            </div>

            <!-- Tombol Tandai Semua Dibaca -->
            <div>
                <template x-if="unreadCount > 0">
                    <button type="button"
                            @click="markAllAsRead()"
                            class="text-[10px] font-semibold text-[#166534] hover:text-[#14532D] hover:underline bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded-md transition inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Tandai Dibaca</span>
                    </button>
                </template>
                <template x-if="unreadCount === 0">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                        Semua Terbaca
                    </span>
                </template>
            </div>
        </div>

        <!-- Daftar 15 Notifikasi Gabungan (FIFO Feed) -->
        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
            <template x-for="item in feed" :key="item.id">
                <div @click="handleItemClick(item)"
                     :class="getItemClass(item)"
                     class="p-3 transition cursor-pointer relative group">
                    
                    <div class="flex items-start gap-3">
                        <!-- Icon Sesuai Kategori -->
                        <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center transition"
                             :class="getIconBgClass(item)">
                            
                            <!-- Icon Stok Menipis / Kritis -->
                            <template x-if="item.icon === 'alert'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>

                            <!-- Icon Barang Masuk -->
                            <template x-if="item.icon === 'truck'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                            </template>

                            <!-- Icon Shift Kasir -->
                            <template x-if="item.icon === 'clock'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>

                            <!-- Icon Penjualan / Transaksi -->
                            <template x-if="item.icon === 'receipt'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            </template>

                            <!-- Icon Void Penjualan -->
                            <template x-if="item.icon === 'x-circle'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>

                            <!-- Icon Penyesuaian Stok -->
                            <template x-if="item.icon === 'scale'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3-1m12 0l-3 1m0 0l3 9a5.002 5.002 0 01-6.001 0M18 7l-3-1m-6 4v10m0 0h6m-6 0H9" />
                                </svg>
                            </template>

                            <!-- Icon Data Barang -->
                            <template x-if="item.icon === 'tag'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </template>

                            <!-- Icon Default Lonceng -->
                            <template x-if="!['alert', 'truck', 'clock', 'receipt', 'x-circle', 'scale', 'tag'].includes(item.icon)">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </template>
                        </div>

                        <!-- Konten Notifikasi -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-[9px] px-1.5 py-0.5 rounded uppercase tracking-wider font-semibold"
                                      :class="getBadgeClass(item)"
                                      x-text="item.badge">
                                </span>
                                
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <!-- Titik Dot Belum Dibaca -->
                                    <template x-if="!isRead(item.id)">
                                        <span class="w-2 h-2 rounded-full shrink-0" :class="getDotClass(item)"></span>
                                    </template>
                                    <span class="text-[10px] text-gray-400 font-medium" x-text="item.waktu"></span>
                                </div>
                            </div>

                            <p class="text-xs font-bold leading-snug line-clamp-1"
                               :class="isRead(item.id) ? 'text-gray-800' : 'text-gray-950'"
                               x-text="item.judul">
                            </p>

                            <p class="text-[11px] leading-relaxed mt-0.5 line-clamp-2"
                               :class="isRead(item.id) ? 'text-gray-500' : 'text-gray-700'"
                               x-text="item.pesan">
                            </p>

                            <!-- Petugas & Tautan -->
                            <div class="flex items-center justify-between mt-1 text-[10px] text-gray-400">
                                <span x-show="item.petugas" x-text="`Oleh: ${item.petugas}`"></span>
                                <span x-show="item.url" class="text-[#166534] font-semibold group-hover:underline flex items-center gap-0.5 ml-auto">
                                    <span>Buka</span>
                                    <span>&rarr;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- State Kosong (Belum Ada Notifikasi Apapun) -->
            <div x-show="feed.length === 0" class="py-10 px-4 text-center">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-xs font-bold text-gray-900">Belum Ada Notifikasi Masuk</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Peringatan stok barang dan aktivitas operasional kios akan tampil di sini secara otomatis.</p>
            </div>
        </div>

    </div>
</div>
