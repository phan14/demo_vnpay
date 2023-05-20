<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Order extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('size_model');
        $this->load->model('province_model');
        $this->load->model('district_model');
        $this->load->model('product_model');
        $this->load->model('sizedetail_model');
        $this->load->model('ward_model');
        $this->load->model('shipping_model');
        $this->load->library('form_validation');
        $this->load->helper('form');
    }

    public function index()
    {


        $this->form_validation->set_error_delimiters('<div class="alert alert-danger" role="alert" style="padding:5px;border-bottom:0px;">', '</div>');

        $carts = $this->cart->contents();


        $total_amount = 0;
        foreach ($carts as $value) {
            $total_amount = $total_amount + $value['subtotal'];
        }
        $this->data['total_amount'] = $total_amount;
        $user_id = 0;
        if ($this->session->userdata('user')) {
            $user = $this->session->userdata('user');
            $user_id = $user->id;
        }
        $input = array();
        $input['shipping'] = array('id', 'ASC');
        $shipping = $this->shipping_model->get_list($input);
        $this->data['shipping'] = $shipping;

        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Họ tên', 'required');
            $this->form_validation->set_rules('phone', 'Điện thoại', 'required');
            $this->form_validation->set_rules('province', 'Tỉnh,thành phố', 'required');
            $this->form_validation->set_rules('district', 'Quận,Huyện', 'required');
            $this->form_validation->set_rules('ward', 'Xã,Phường', 'required');
            $this->form_validation->set_rules('ship_money', 'Dịch vụ vận chuyển', 'required');

            if ($this->form_validation->run()) {


                $adress_str = $this->input->post('adress') . ' - ' . $this->input->post('area');

                $ship = $this->input->post('ship_money');

                $mess2 = 'Phí Ship:' . ' ' . strval(number_format($ship)) . 'VNĐ';

                $transaction = array();
                $transaction = array(
                    'user_id' => $user_id,
                    'user_name' => $this->input->post('name'),
                    'user_email' => $this->input->post('email'),
                    'user_address' => $adress_str,
                    'user_phone' => $this->input->post('phone'),
                    'message' => $this->input->post('message') . " " . $mess2,
                    'amount' => $total_amount + $ship,
                    'created' => now()
                );
                $_SESSION["transaction"] = $transaction;
                // $this->load->model('transaction_model');
                // $this->transaction_model->create($data);
                // $transaction_id = $this->db->insert_id();

                // $this->load->model('order_model');
                // foreach ($carts as $items) {
                //     $data = array();
                //     $data = array(
                //         'transaction_id' => $transaction_id,
                //         'product_id' => $items['id'],
                //         'qty' => $items['qty'],
                //         'amount' => $items['subtotal'],
                //         'size_id' => '0'.intval($items['size'])
                //     );
                //     $this->order_model->create($data);
                //     //Cộng lượt mua
                //     $product = $this->product_model->get_info($items['id']);
                //     $data4 = array();
                //     $sl = $product->buyed + intval($items['qty']);
                //     $data4['buyed'] = $sl;
                //     $this->product_model->update($product->id, $data4);
                //     //trừ số lượng
                //     $input1['where'] = array('product_id' => $items['id'], 'size_id' => $items['size']);
                //     $size_detail = $this->sizedetail_model->get_list($input1);
                //     if (sizeof($size_detail) != 0) {
                //         $id_update_size = $size_detail[0]->id;
                //         $amount = $size_detail[0]->quantity - $items['qty'];
                //         if ($id_update_size != 0 && $amount > 0) {
                //             $data2 = array();
                //             $data2 = array(
                //                 'product_id' => $items['id'],
                //                 'size_id' => '0'.intval($items['size']),
                //                 'quantity' => $amount,
                //             );
                //             $this->sizedetail_model->update($id_update_size, $data2);
                //         } elseif ($id_update_size != 0 && $amount == 0) {
                //             $this->sizedetail_model->delete($id_update_size);
                //         }
                //     }
                // }
                // $this->cart->destroy();
                // $this->session->set_flashdata('message', "Đặt hàng thành công, chúng tôi sẽ liên hệ với bạn để giao hàng");
                redirect(base_url('order/payment'));
            }
        }



        $this->data['temp'] = 'site/order/index';
        $this->load->view('site/layoutsub', $this->data);
    }

    public function payment()
    {

        $carts = $this->cart->contents();
        $user_id = 0;
        if ($this->session->userdata('user')) {
            $user = $this->session->userdata('user');
            $user_id = $user->id;
        }
        $input = array();
        $input['shipping'] = array('id', 'ASC');
        $shipping = $this->shipping_model->get_list($input);
        $this->data['shipping'] = $shipping;

        if ($this->input->post()) {
            $payment_method = $this->input->post("payment_method");
            $data = array();
            $data = array(
                'user_id' => $user_id,
                'user_name' => $_SESSION["transaction"]["user_name"],
                'user_email' => $_SESSION["transaction"]["user_email"],
                'user_address' => $_SESSION["transaction"]["user_address"],
                'user_phone' => $_SESSION["transaction"]["user_phone"],
                'message' => $_SESSION["transaction"]["message"],
                'amount' => $_SESSION["transaction"]["amount"],
                "payment" => $payment_method,
                'created' => now()
            );
            $result_set = $this->db->select("id")->order_by("id", "DESC")->from("order")->get();
            $result_object = $result_set->row();
            if (!empty($result_object->id)) {
                $maxId = $result_object->id;
            } else {
                $maxId = 1;
            }
            $code = "HANDEE" . ($maxId + 1);
            //thanh toan tien mat
            if ($payment_method === "cash") {
                $this->load->model('transaction_model');
                $this->transaction_model->create($data);
                $transaction_id = $this->db->insert_id();

                $this->load->model('order_model');
                foreach ($carts as $items) {
                    $data = array();
                    $data = array(
                        'transaction_id' => $transaction_id,
                        'product_id' => $items['id'],
                        "code" => $code,
                        'qty' => $items['qty'],
                        'amount' => $items['subtotal'],
                        'size_id' => '0' . intval($items['size']),
                        "payment_method" => $payment_method
                    );
                    $this->order_model->create($data);
                    //Cộng lượt mua
                    $product = $this->product_model->get_info($items['id']);
                    $data4 = array();
                    $sl = $product->buyed + intval($items['qty']);
                    $data4['buyed'] = $sl;
                    $this->product_model->update($product->id, $data4);
                    //trừ số lượng
                    $input1['where'] = array('product_id' => $items['id'], 'size_id' => $items['size']);
                    $size_detail = $this->sizedetail_model->get_list($input1);
                    if (sizeof($size_detail) != 0) {
                        $id_update_size = $size_detail[0]->id;
                        $amount = $size_detail[0]->quantity - $items['qty'];
                        if ($id_update_size != 0 && $amount > 0) {
                            $data2 = array();
                            $data2 = array(
                                'product_id' => $items['id'],
                                'size_id' => '0' . intval($items['size']),
                                'quantity' => $amount,
                            );
                            $this->sizedetail_model->update($id_update_size, $data2);
                        } elseif ($id_update_size != 0 && $amount == 0) {
                            $this->sizedetail_model->delete($id_update_size);
                        }
                    }
                }
                $this->cart->destroy();
                $vnp_Returnurl = "http://handee.com/shopbanquanao/order/thanks";
                header('Location: ' . $vnp_Returnurl);
            } elseif ($payment_method === "vnpay") {
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                /*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

                $vnp_TmnCode = "7GXD782M"; //Website ID in VNPAY System
                $vnp_HashSecret = "ICZOFUFSWQXOIQYVXWWLVMPGHLJKHOGA"; //Secret key
                $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                $vnp_Returnurl = "http://handee.com/shopbanquanao/order/thanks";
                $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
                //Config input format
                //Expire
                $startTime = date("YmdHis");
                $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

                $vnp_TxnRef = $code; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
                $vnp_OrderInfo = "Thanh toan don hang tai web";
                $vnp_OrderType = "billpayment";
                $vnp_Amount = $_SESSION["transaction"]["amount"] * 100;
                $vnp_Locale = "vn";
                $vnp_BankCode = "NCB";
                $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
                //Add Params of 2.0.1 Version
                $vnp_ExpireDate = $expire;

                $inputData = array(
                    "vnp_Version" => "2.1.0",
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_Command" => "pay",
                    "vnp_CreateDate" => date('YmdHis'),
                    "vnp_CurrCode" => "VND",
                    "vnp_IpAddr" => $vnp_IpAddr,
                    "vnp_Locale" => $vnp_Locale,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_OrderType" => $vnp_OrderType,
                    "vnp_ReturnUrl" => $vnp_Returnurl,
                    "vnp_TxnRef" => $vnp_TxnRef,
                    "vnp_ExpireDate" => $vnp_ExpireDate
                );

                if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                    $inputData['vnp_BankCode'] = $vnp_BankCode;
                }
                // if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                //     $inputData['vnp_Bill_State'] = $vnp_Bill_State;
                // }

                //var_dump($inputData);
                ksort($inputData);
                $query = "";
                $i = 0;
                $hashdata = "";
                foreach ($inputData as $key => $value) {
                    if ($i == 1) {
                        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashdata .= urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                    $query .= urlencode($key) . "=" . urlencode($value) . '&';
                }

                $vnp_Url = $vnp_Url . "?" . $query;
                if (isset($vnp_HashSecret)) {
                    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
                    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                }
                $returnData = array(
                    'code' => '00', 'message' => 'success', 'data' => $vnp_Url
                );
                if (isset($_POST['redirect'])) {
                    $_SESSION["carts"] = $carts;
                    $_SESSION["user_id"] = $user_id;
                    $_SESSION["code"] = $code;
                    $_SESSION["payment_method"] = $payment_method;

                    header('Location: ' . $vnp_Url);
                    die();
                } else {
                    echo json_encode($returnData);
                }
                // vui lòng tham khảo thêm tại code demo
            }
        }
        $this->data['carts'] = $carts;
        $this->data['temp'] = 'site/order/payment';
        $this->load->view('site/layoutsub', $this->data);
    }

    public function thanks()
    {
        if (isset($_GET["vnp_Amount"])) {
            $data = array();
            $data = array(
                "vnp_amount" => $_GET["vnp_Amount"],
                "vnp_bankCode" => $_GET["vnp_BankCode"],
                "vnp_banktranno" => $_GET["vnp_BankTranNo"],
                "vnp_orderinfo" => $_GET["vnp_OrderInfo"],
                "vnp_paydate" => $_GET["vnp_PayDate"],
                "vnp_tmncode" => $_GET["vnp_TmnCode"],
                "vnp_transactionno" => $_GET["vnp_TransactionNo"],
                "vnp_cardtype" => $_GET["vnp_CardType"],
                "code_cart" => $_SESSION["code"]
            );

            $this->load->model('vnpay_model');
            $this->vnpay_model->create($data);

            $transaction = array(
                'user_id' => $_SESSION["user_id"],
                'user_name' => $_SESSION["transaction"]["user_name"],
                'user_email' => $_SESSION["transaction"]["user_email"],
                'user_address' => $_SESSION["transaction"]["user_address"],
                'user_phone' => $_SESSION["transaction"]["user_phone"],
                'message' => $_SESSION["transaction"]["message"],
                'amount' => $_SESSION["transaction"]["amount"],
                "payment" => $_SESSION["payment_method"],
                'created' => now()
            );
            $this->load->model('transaction_model');
            $this->transaction_model->create($transaction);
            $transaction_id = $this->db->insert_id();

            $this->load->model('order_model');
            foreach ($_SESSION["carts"] as $items) {
                $data = array();
                $data = array(
                    'transaction_id' => $transaction_id,
                    'product_id' => $items['id'],
                    "code" => $_SESSION["code"],
                    'qty' => $items['qty'],
                    'amount' => $items['subtotal'],
                    'size_id' => '0' . intval($items['size']),
                    "payment_method" => $_SESSION["payment_method"]
                );
                $this->order_model->create($data);
                //Cộng lượt mua
                $product = $this->product_model->get_info($items['id']);
                $data4 = array();
                $sl = $product->buyed + intval($items['qty']);
                $data4['buyed'] = $sl;
                $this->product_model->update($product->id, $data4);
                //trừ số lượng
                $input1['where'] = array('product_id' => $items['id'], 'size_id' => $items['size']);
                $size_detail = $this->sizedetail_model->get_list($input1);
                if (sizeof($size_detail) != 0) {
                    $id_update_size = $size_detail[0]->id;
                    $amount = $size_detail[0]->quantity - $items['qty'];
                    if ($id_update_size != 0 && $amount > 0) {
                        $data2 = array();
                        $data2 = array(
                            'product_id' => $items['id'],
                            'size_id' => '0' . intval($items['size']),
                            'quantity' => $amount,
                        );
                        $this->sizedetail_model->update($id_update_size, $data2);
                    } elseif ($id_update_size != 0 && $amount == 0) {
                        $this->sizedetail_model->delete($id_update_size);
                    }
                }
            }
            $this->cart->destroy();
            $response = "<h3>Giao dịch thanh toán bằng VNPAY thành công</h3>";
            $this->data['response'] = $response;
        }
        $this->data['temp'] = 'site/order/thanks';
        $this->load->view('site/layoutsub', $this->data);
    }
}
