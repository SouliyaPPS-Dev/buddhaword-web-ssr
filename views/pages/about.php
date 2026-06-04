<section class="text-lg py-10 px-4 md:px-10 mx-auto rounded-lg mb-10 max-w-5xl">
    <!-- Logo + Title -->
    <div class="flex flex-col items-center mb-8">
        <img src="<?= url('logo_wutdarn.png') ?>" alt="ວັດປ່າດານພຣະ logo" class="w-40 h-auto rounded-xl shadow-md">
    </div>

    <!-- About Section -->
    <div class="text-center mb-12 bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm">
        <p class="leading-relaxed md:leading-loose text-gray-800 md:text-xl font-medium">
            ເເອັບຄຳສອນພຣະພຸດທະເຈົ້າ,
            ສ້າງຂື້ນເພື່ອເຜີຍແຜ່ໃຫ້ພວກເຮົາທັງຫຼາຍໄດ້ສຶກສາ ແລະ ປະຕິບັດຕາມ,
            ດັ່ງທີ່ພຣະຕະຖາຄົດກ່າວວ່າ "ທຳມະຍິ່ງເປີດເຜີຍຍິ່ງຮຸ່ງເຮືອງ".
            ເມື່ອໄດ້ສຶກສາ ແລະ ປະຕິບັດຕາມ ຈົນເຫັນທຳມະຊາດຕາມຄວາມເປັນຈິງ
            ກໍຈະຫຼຸດພົ້ນຈາກຄວາມທຸກທັງປວງ.
        </p>
    </div>

    <!-- Facebook Pages -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-center mb-8 text-[#DDCFBC]">Facebook Pages</h2>
        <div class="grid grid-cols-4 gap-4 items-center justify-items-center">
            <?php
            $fbPages = [
                ['src' => 'logo_wutdarn.png', 'alt' => 'ວັດປ່າດານພຣະ', 'href' => 'https://web.facebook.com/watdanpra'],
                ['src' => 'dhammakonnon.png', 'alt' => 'ທັມມະກ່ອນນອນ', 'href' => 'https://web.facebook.com/dhammakonnon'],
                ['src' => 'ຮຸ່ງເເສງເເຫ່ງທັມ.png', 'alt' => 'ຮຸ່ງແສງແຫ່ງທັມ', 'href' => 'https://www.facebook.com/Sumittosumittabounsong'],
                ['src' => 'ຕະຖາຄົຕພາສິຕ.png', 'alt' => 'ຕະຖາຄົດພາສິດ', 'href' => 'https://web.facebook.com/watpavimokkhavanaram.la'],
                ['src' => 'ພຸທທະວົງສ໌.png', 'alt' => 'ພຸທທະວົງສ໌', 'href' => 'https://www.facebook.com/dhammalife.laos'],
                ['src' => 'ວິນັຍສຸຄົຕ.png', 'alt' => 'ວິນັຍສຸຄົຕ', 'href' => 'https://www.facebook.com/profile.php?id=100091798479187'],
                ['src' => 'ວັດບ້ານນາຈິກ.png', 'alt' => 'ວັດບ້ານນາຈິກ', 'href' => 'https://www.facebook.com/phouhuck.phousamnieng.7'],
                ['src' => 'buddhaword.png', 'alt' => 'ຄຳສອນພຣະພຸດທະເຈົ້າ', 'href' => 'https://web.facebook.com/profile.php?id=100077638042542'],
            ];
            foreach ($fbPages as $page): ?>
                <a href="<?= $page['href'] ?>" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform">
                    <img src="<?= url($page['src']) ?>" alt="<?= $page['alt'] ?>" class="w-20 h-20 object-contain">
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-3xl shadow-sm border border-white/20">
        <h2 class="text-2xl font-bold text-center mb-8 text-[#795548]">ຕິດຕໍ່</h2>
        <div class="flex flex-col items-center space-y-6">
            <div class="flex flex-col space-y-4 w-full max-w-md">
                <a href="https://wa.me/8562056118850" target="_blank" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-green-50 transition-colors">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.29-4.143c1.589.943 3.13 1.411 4.755 1.412 5.384 0 9.763-4.379 9.765-9.764.001-2.61-1.017-5.064-2.868-6.913-1.851-1.848-4.307-2.864-6.918-2.865-5.385 0-9.763 4.38-9.766 9.764-.001 1.831.503 3.607 1.458 5.175l-.95 3.47 3.556-.934zm11.411-5.005c-.27-.136-1.592-.785-1.839-.875-.246-.089-.426-.135-.606.136-.18.271-.696.875-.853 1.056-.157.181-.314.204-.584.068-.27-.136-1.14-.42-2.171-1.339-.802-.715-1.343-1.598-1.5-1.87-.157-.271-.017-.417.118-.553.121-.122.271-.316.406-.474.135-.158.18-.271.271-.452.09-.181.045-.339-.022-.474-.068-.136-.606-1.464-.83-2.003-.219-.53-.439-.459-.606-.468-.157-.009-.338-.01-.517-.01-.179 0-.472.067-.719.338-.247.271-.943.923-.943 2.253s.967 2.615 1.102 2.796c.135.181 1.902 2.903 4.608 4.07.643.277 1.145.443 1.536.566.647.206 1.235.177 1.7.108.52-.077 1.592-.65 1.817-1.278.225-.628.225-1.166.157-1.277-.067-.112-.247-.204-.517-.34z"/></svg>
                    </div>
                    <span class="font-bold text-gray-700">+8562056118850</span>
                </a>
                <a href="https://wa.me/8562078287509" target="_blank" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-green-50 transition-colors">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.29-4.143c1.589.943 3.13 1.411 4.755 1.412 5.384 0 9.763-4.379 9.765-9.764.001-2.61-1.017-5.064-2.868-6.913-1.851-1.848-4.307-2.864-6.918-2.865-5.385 0-9.763 4.38-9.766 9.764-.001 1.831.503 3.607 1.458 5.175l-.95 3.47 3.556-.934zm11.411-5.005c-.27-.136-1.592-.785-1.839-.875-.246-.089-.426-.135-.606.136-.18.271-.696.875-.853 1.056-.157.181-.314.204-.584.068-.27-.136-1.14-.42-2.171-1.339-.802-.715-1.343-1.598-1.5-1.87-.157-.271-.017-.417.118-.553.121-.122.271-.316.406-.474.135-.158.18-.271.271-.452.09-.181.045-.339-.022-.474-.068-.136-.606-1.464-.83-2.003-.219-.53-.439-.459-.606-.468-.157-.009-.338-.01-.517-.01-.179 0-.472.067-.719.338-.247.271-.943.923-.943 2.253s.967 2.615 1.102 2.796c.135.181 1.902 2.903 4.608 4.07.643.277 1.145.443 1.536.566.647.206 1.235.177 1.7.108.52-.077 1.592-.65 1.817-1.278.225-.628.225-1.166.157-1.277-.067-.112-.247-.204-.517-.34z"/></svg>
                    </div>
                    <span class="font-bold text-gray-700">+8562078287509</span>
                </a>
                <a href="https://tawk.to/chat/61763b9bf7c0440a591fc969/1fiqthn3u" target="_blank" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-blue-50 transition-colors">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <span class="font-bold text-gray-700">ຕິດຕໍ່ Admin</span>
                </a>
            </div>
        </div>
    </div>
</section>
