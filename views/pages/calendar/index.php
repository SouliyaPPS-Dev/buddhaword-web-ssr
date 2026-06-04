<section class="p-4" x-data="{
    currentDate: new Date(),
    events: [],
    get daysInMonth() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        return new Date(year, month + 1, 0).getDate();
    },
    get firstDayOfMonth() {
        return new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1).getDay();
    },
    get monthName() {
        const months = ['ມັງກອນ', 'ກຸມພາ', 'ມີນາ', 'ເມສາ', 'ພຶດສະພາ', 'ມິຖຸນາ', 'ກໍລະກົດ', 'ສິງຫາ', 'ກັນຍາ', 'ຕຸລາ', 'ພະຈິກ', 'ທັນວາ'];
        return months[this.currentDate.getMonth()];
    },
    get calendarDays() {
        const days = [];
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDay = new Date(year, month, 1).getDay();
        const prevMonthLastDay = new Date(year, month, 0).getDate();
         
        /* Previous month days */
        for (let i = firstDay - 1; i >= 0; i--) {
            const d = new Date(year, month - 1, prevMonthLastDay - i);
            days.push({ 
                day: d.getDate(), 
                dateStr: this.formatDate(d),
                currentMonth: false 
            });
        }
        
        /* Current month days */
        for (let i = 1; i <= this.daysInMonth; i++) {
            const d = new Date(year, month, i);
            days.push({ 
                day: i, 
                dateStr: this.formatDate(d),
                currentMonth: true 
            });
        }
        
        /* Next month days */
        const remaining = 42 - days.length;
        for (let i = 1; i <= remaining; i++) {
            const d = new Date(year, month + 1, i);
            days.push({ 
                day: i, 
                dateStr: this.formatDate(d),
                currentMonth: false 
            });
        }
        
        return days;
    },
    formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    },
    getEventsForDay(dateStr) {
        return this.events.filter(e => {
            const start = e.startDateISO;
            const end = e.endDateISO || start;
            if (!start) return false;
            return dateStr >= start && dateStr <= end;
        });
    },
    prevMonth() {
        this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
    },
    nextMonth() {
        this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
    },
    init() {
        const cached = localStorage.getItem('buddhaword_calendar');
        if (cached) {
            try { this.events = JSON.parse(cached); } catch (e) {}
        }
        if (!this.events || this.events.length === 0) {
            const dataEl = document.getElementById('events-data');
            if (dataEl) {
                try { this.events = JSON.parse(dataEl.textContent); } catch (e) { console.error('Failed to parse events data', e); }
            }
        }
        window.dispatchEvent(new CustomEvent('app-data-ready'));

        window.addEventListener('sync-complete', () => {
            const fresh = localStorage.getItem('buddhaword_calendar');
            if (fresh) { try { this.events = JSON.parse(fresh); } catch (e) {} }
        });
    }
}">
    <!-- Events Data -->
    <script id="events-data" type="application/json">
        <?= json_encode($events, JSON_UNESCAPED_UNICODE) ?>
    </script>
    <!-- Calendar Header -->
    <div class="max-w-6xl mx-auto bg-white rounded-2xl sm:rounded-3xl shadow-xl overflow-hidden mb-6 md:mb-8">
        <div class="bg-[#795548] p-4 sm:p-6 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1 sm:gap-2">
                <select @change="currentDate = new Date(currentDate.getFullYear(), $event.target.value, 1)" 
                        class="bg-transparent text-xl sm:text-2xl font-bold Lao-font outline-none cursor-pointer hover:bg-white/10 rounded px-1 sm:px-2 appearance-none">
                    <template x-for="(month, index) in ['ມັງກອນ', 'ກຸມພາ', 'ມີນາ', 'ເມສາ', 'ພຶດສະພາ', 'ມິຖຸນາ', 'ກໍລະກົດ', 'ສິງຫາ', 'ກັນຍາ', 'ຕຸລາ', 'ພະຈິກ', 'ທັນວາ']" :key="index">
                        <option :value="index" :selected="index === currentDate.getMonth()" x-text="month" class="text-gray-800"></option>
                    </template>
                </select>
                <select @change="currentDate = new Date($event.target.value, currentDate.getMonth(), 1)" 
                        class="bg-transparent text-xl sm:text-2xl font-bold Lao-font outline-none cursor-pointer hover:bg-white/10 rounded px-1 sm:px-2 appearance-none">
                    <template x-for="year in Array.from({length: 10}, (_, i) => new Date().getFullYear() - 2 + i)" :key="year">
                        <option :value="year" :selected="year === currentDate.getFullYear()" x-text="year" class="text-gray-800"></option>
                    </template>
                </select>
            </div>
            <div class="flex gap-2 w-full sm:w-auto justify-center">
                <button @click="prevMonth()" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button @click="currentDate = new Date()" class="px-4 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs sm:text-sm Lao-font transition-colors">ເດືອນນີ້</button>
                <button @click="nextMonth()" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 border-b border-gray-100 overflow-x-auto">
            <template x-for="day in ['ອາ.', 'ຈ.', 'ອ.', 'ພ.', 'ພຫ.', 'ສ.', 'ເສົາ']">
                <div class="p-2 sm:p-4 text-center font-bold text-gray-500 text-[10px] sm:text-sm border-r last:border-0 Lao-font bg-gray-50" x-text="day"></div>
            </template>
        </div>
        
        <div class="grid grid-cols-7 min-h-[400px] sm:min-h-[600px]">
            <template x-for="(date, index) in calendarDays" :key="index">
                <div class="border-b border-r last:border-r-0 p-1 sm:p-2 flex flex-col gap-0.5 sm:gap-1 transition-colors hover:bg-gray-50"
                     :class="{ 
                         'bg-gray-50/50': !date.currentMonth,
                         'bg-brown-50/30 ring-1 sm:ring-2 ring-brown-500 ring-inset': date.dateStr === formatDate(new Date())
                     }">
                    <span class="text-[10px] sm:text-sm font-bold" 
                          :class="{
                              'text-brown-600': date.dateStr === formatDate(new Date()),
                              'text-gray-700': date.currentMonth && date.dateStr !== formatDate(new Date()),
                              'text-gray-300': !date.currentMonth
                          }" 
                          x-text="date.day"></span>
                    
                    <div class="flex flex-col gap-0.5 sm:gap-1">
                        <template x-for="event in getEventsForDay(date.dateStr)" :key="event.ID + '-' + date.dateStr">
                            <a :href="'<?= url('/calendar/view/') ?>' + event.ID" 
                               class="text-[8px] sm:text-[10px] p-0.5 sm:p-1 rounded leading-tight hover:bg-brown-600 hover:text-white transition-all Lao-font overflow-hidden text-ellipsis whitespace-nowrap"
                               :class="{
                                   'bg-brown-500 text-white font-bold border-l-2 sm:border-l-4 border-brown-800 shadow-sm': date.dateStr === event.startDateISO,
                                   'bg-brown-100 text-brown-700 border-l border-white': date.dateStr > event.startDateISO
                               }"
                               :title="event.title"
                               x-text="event.title"></a>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Upcoming Events List -->
    <div class="max-w-6xl mx-auto">
        <h3 class="text-2xl font-bold text-white mb-6 Lao-font drop-shadow-md">ກິດຈະກຳທັງໝົດ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach (array_reverse($events) as $event): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="aspect-video overflow-hidden relative group">
                        <img src="<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                             loading="lazy"
                             onerror="this.src='<?= url('assets/images/logo.png') ?>'; this.className='w-full h-full object-contain p-8 bg-gray-50'">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-xs font-bold text-[#795548] mb-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <?= htmlspecialchars($event['startDateTime']) ?>
                            </div>
                            <h4 class="font-bold text-gray-800 mb-2 Lao-font line-clamp-2"><?= htmlspecialchars($event['title']) ?></h4>
                        </div>
                        <a href="<?= url('/calendar/view/' . $event['ID']) ?>" class="mt-4 block text-center py-2 bg-[#DDCFBC] text-[#795548] rounded-xl font-bold text-sm hover:bg-[#795548] hover:text-white transition-colors">
                            ເບິ່ງລາຍລະອຽດ
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

       