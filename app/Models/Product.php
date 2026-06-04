<?php
namespace App\Models;

class Product extends Model {
    protected $table = 'products';

    /**
     * For demonstration without a real DB table yet, 
     * we can override all() and find() or just use them once the table exists.
     */
    public function getMockProducts() {
        return [
            ['id' => 1, 'name' => 'ເສື້ອໄໝລາວ', 'price' => 250000, 'description' => 'ເສື້ອໄໝລາວຄຸນນະພາບດີ, ຜະລິດຈາກໄໝທຳມະຊາດ.'],
            ['id' => 2, 'name' => 'ສິ້ນໄໝ', 'price' => 450000, 'description' => 'ສິ້ນໄໝລາວລວດລາຍສວຍງາມ, ເໝາະສຳລັບງານບຸນ.'],
        ];
    }

    public function findMock($id) {
        $products = $this->getMockProducts();
        foreach ($products as $product) {
            if ($product['id'] == $id) return $product;
        }
        return null;
    }
}
