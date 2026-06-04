<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-center text-blue-600 mb-4">ຍິນດີຕ້ອນຮັບສູ່ BuddhaWord</h1>
    <p class="text-lg text-center text-gray-700 mb-12">ເວັບໄຊທ໌ສຳລັບຮຽນຮູ້ຄຳສອນຂອງພຣະພຸດທະເຈົ້າ ແລະ ເລືອກຊື້ເຄື່ອງໄໝລາວ.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 transition-transform hover:scale-105">
                <div class="h-48 bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400">ຮູບພາບສິນຄ້າ</span>
                </div>
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h2>
                    <p class="text-blue-600 font-bold mb-4"><?= number_format($product['price']) ?> ກີບ</p>
                    <p class="text-gray-600 text-sm mb-6 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                    <a href="<?= url('/product/' . $product['id']) ?>" 
                       class="block text-center bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        ເບິ່ງລາຍລະອຽດ
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
