<?php

namespace Test\Service;

use PHPUnit\Framework\TestCase;
use App\Service\ProductHandler;
use function AlibabaCloud\Client\envNotEmpty;

/**
 * Class ProductHandlerTest
 */
class ProductHandlerTest extends TestCase
{
    const SEARCH_DESSERT_PRODUCT = 'Dessert';
    const SEARCH_PRODUCT_SORT_DESC = SORT_DESC;
    private $products = [
        [
            'id' => 1,
            'name' => 'Coca-cola',
            'type' => 'Drinks',
            'price' => 10,
            'create_at' => '2021-04-20 10:00:00',
        ],
        [
            'id' => 2,
            'name' => 'Persi',
            'type' => 'Drinks',
            'price' => 5,
            'create_at' => '2021-04-21 09:00:00',
        ],
        [
            'id' => 3,
            'name' => 'Ham Sandwich',
            'type' => 'Sandwich',
            'price' => 45,
            'create_at' => '2021-04-20 19:00:00',
        ],
        [
            'id' => 4,
            'name' => 'Cup cake',
            'type' => 'Dessert',
            'price' => 35,
            'create_at' => '2021-04-18 08:45:00',
        ],
        [
            'id' => 5,
            'name' => 'New York Cheese Cake',
            'type' => 'Dessert',
            'price' => 40,
            'create_at' => '2021-04-19 14:38:00',
        ],
        [
            'id' => 6,
            'name' => 'Lemon Tea',
            'type' => 'Drinks',
            'price' => 8,
            'create_at' => '2021-04-04 19:23:00',
        ],
    ];

    /*
     *@DESC ��ȡ��Ʒ�ܽ��
     * @author  li
     * @date 20221230
     * @param array  ��Ʒ��Ϣ
     * @return float �ܽ��
     */
    public function testGetTotalPrice()
    {
        $totalPrice = 0;
        foreach ($this->products as $product) {
            $price = $product['price'] ?: 0;
            if($price){
                $totalPrice = bcadd($totalPrice, $price,0);
            }

        }

        return $totalPrice;

//        $this->assertEquals(143, $totalPrice);
    }

    /*
     *@DESC ��ȡ����Ϊdessert����Ʒ���Ұ�����
     * @author  li
     * @date 20221230
     * @param array  ��Ʒ��Ϣ
     * @return array dessert����Ʒ
     */
    public function testDessertProduct()
    {
        $returnData = $sortData  = [];
        foreach ($this->products as &$product) {
            if($product['type'] == self::SEARCH_DESSERT_PRODUCT){
                //������Ϊdessert����Ʒ�ŵ�һ��
                $sortData[] = $product;
            }
        }
         //���������
        $sort  = array_column($sortData, 'price');
        $returnData = $this->getSort($sort, self::SEARCH_PRODUCT_SORT_DESC, $sortData);
        return $returnData;
    }

    /*
     *@DESC ��ȡ��Ʒ��Ϣ������ʱ���Ϊʱ���
     * @author  li
     * @date 20221230
     * @param array  �����ֶ�
     * @param string  �������
     * @param array ����������
     * @return  array
     */
    public function testUnixProduct(){
        foreach ($this->products as &$product) {
            if(!empty($product['create_at'])){
                $product['create_at'] = strtotime($product['create_at']);
            }
        }
        return $this->products;
    }

    /*
     *@DESC ��ά�������ĳ���ֶ�����
     * @author  li
     * @date 20221230
     * @param array  �����ֶ�
     * @param string  �������
     * @param array ����������
     * @return  array
     */
    public static function getSort($sort, $sortRule, $sortData){
        if(!empty($sort) && !empty($sortRule) && !empty($sortData)){
            array_multisort($sort, $sortRule, $sortData);
        }
        return $sortData;
    }
}