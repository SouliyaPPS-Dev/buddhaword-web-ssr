<div class="container mx-auto px-4 py-8">
    <nav class="mb-4">
        <a href="<?= url('/') ?>" class="text-blue-600 hover:underline">← ກັບຄືນໜ້າຫຼັກ</a>
    </nav>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col md:flex-row">
        <div class="md:w-1/2 bg-gray-200 h-64 md:h-auto flex items-center justify-center">
            <span class="text-gray-400">ຮູບພາບສິນຄ້າ</span>
        </div>
        <div class="md:w-1/2 p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-2xl text-blue-600 font-semibold mb-6">
                <?= number_format($product['price']) ?> ກີບ
            </p>
            <div class="prose max-w-none text-gray-700 mb-8">
                <p><?= htmlspecialchars($product['description']) ?></p>
            </div>
            
            <button onclick="Swal.fire('ສຳເລັດ', 'ເພີ່ມລົງໃນກະຕ່າແລ້ວ', 'success')" 
                    class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                ເພີ່ມລົງໃນກະຕ່າ
            </button>
        </div>
    </div>
</div>
