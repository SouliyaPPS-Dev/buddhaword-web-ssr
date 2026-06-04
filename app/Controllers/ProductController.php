<?php
namespace App\Controllers;

class ProductController {
    public function show($id) {
        // Mock data - In a real app, this would come from a Model
        $products = [
            '1' => ['name' => 'ເສື້ອໄໝລາວ', 'price' => 250000, 'description' => 'ເສື້ອໄໝລາວຄຸນນະພາບດີ, ຜະລິດຈາກໄໝທຳມະຊາດ.'],
            '2' => ['name' => 'ສິ້ນໄໝ', 'price' => 450000, 'description' => 'ສິ້ນໄໝລາວລວດລາຍສວຍງາມ, ເໝາະສຳລັບງານບຸນ.'],
        ];

        $product = $products[$id] ?? null;

        if (!$product) {
            http_response_code(404);
            echo "ບໍ່ພົບສິນຄ້າ";
            return;
        }
        
        return view('pages.product_detail', [
            'product' => $product,
            'seo' => [
                'title' => $product['name'] . ' | ຄຳສອນພຸດທະ Shop',
                'description' => $product['description'],
                'json_ld' => [
                    "@context" => "https://schema.org/",
                    "@type" => "Product",
                    "name" => $product['name'],
                    "description" => $product['description'],
                    "offers" => [
                        "@type" => "Offer",
                        "price" => $product['price'],
                        "priceCurrency" => "LAK",
                        "availability" => "https://schema.org/InStock"
                    ]
                ]
            ]
        ]);
    }
}
