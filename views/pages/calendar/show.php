<section class="flex flex-col items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden relative">
        <div class="w-full aspect-video overflow-hidden bg-gray-100">
            <img src="<?= htmlspecialchars($event['poster']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-full object-cover" onerror="this.src='<?= url('assets/images/logo.png') ?>'; this.className='w-full h-full object-contain p-8 bg-gray-50'" loading="lazy">
        </div>
        
        <div class="p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 Lao-font"><?= htmlspecialchars($event['title']) ?></h1>
            
            <div class="flex items-center gap-2 text-[#795548] font-bold mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span><?= htmlspecialchars($event['startDateTime']) ?> - <?= htmlspecialchars($event['endDateTime']) ?></span>
            </div>
            
            <div class="prose max-w-none text-gray-700 text-lg Lao-font leading-relaxed">
                <?= nl2br($event['details']) ?>
            </div>
            
            <div class="mt-8 pt-6 border-t flex justify-center">
                <a href="<?= url('/calendar') ?>" class="px-8 py-3 bg-[#DDCFBC] text-[#795548] rounded-2xl font-bold hover:bg-[#795548] hover:text-white transition-all duration-300">
                    ກັບຄືນໄປປະຕິທິນ
                </a>
            </div>
        </div>
    </div>
</section>
