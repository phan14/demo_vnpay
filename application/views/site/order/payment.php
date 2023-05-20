<style>
    .col-md-4.payment_method .form-check {
        margin: 12px;
    }
</style>
<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 clearpaddingr">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Trang chủ</a></li>
            <li class="active">Phương thức thanh toán</li>
        </ol>
        <?php if (isset($message) && !empty($message)) { ?>
            <h4 style="color:green;text-align: center;margin-top: 30px"><?php echo $message; ?></h4>
        <?php } ?>
        <div class="col-md-12 clearpadding">
            <div class="panel panel-info">
                <div class="row">
                    <form action="" method="post">
                        <div class="col-md-8">
                            <h4>Thông tin vận chuyển và thanh toán</h4>
                            <ul>
                                <li>Họ và tên: <b><?php echo $_SESSION["transaction"]["user_name"] ?></b></li>
                                <li>Email: <b><?php echo $_SESSION["transaction"]["user_email"] ?></b></li>
                                <li>Số điện thoại: <b><?php echo $_SESSION["transaction"]["user_phone"] ?></b></li>
                                <li>Địa chỉ: <b><?php echo $_SESSION["transaction"]["user_address"] ?></b></li>
                            </ul>
                            <h5>Giỏ hàng của bạn</h5>
                            <table class="table table-hover">
                                <thead style="background-color: rgb(240, 93, 64);color: #fff;font-size: 14px">
                                    <th>STT</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Hình ảnh</th>
                                    <th style="text-align: center">Số lượng</th>
                                    <th style="text-align: center">Size</th>
                                    <th style="text-align: center">Thêm size mới</th>
                                    <th>Thành tiền</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    $total_price = 0;
                                    foreach ($carts as $items) {
                                        $total_price = $total_price + $items['subtotal'];
                                    ?>
                                        <tr>
                                            <td><?php echo $i = $i + 1 ?></td>
                                            <td><?php echo $items['name']; ?></td>
                                            <td><img src="<?php echo base_url('upload/product/' . $items['image_link']); ?>" class="img-thumbnail" alt="" style="width: 50px;"></td>
                                            <td style="min-width: 150px;text-align: center"><a class="cart-sumsub" href="<?php echo base_url('cart/update/' . $items['cartid'] . '/sub'); ?>">-</a><input type="text" value="<?php echo $items['qty']; ?>" style="width: 30px;text-align: center;"><a class="cart-sumsub" href="<?php echo base_url('cart/update/' . $items['cartid'] . '/sum'); ?>">+</a></td>
                                            <td style="min-width: 150px;padding-left: 40px">
                                                <a class="cart-sumsub" href="<?php echo base_url('cart/sumsize/' . $items['cartid'] . '/' . $items['id']); ?>">-</a>
                                                <input type="hidden" value="<?php echo $items['size']; ?>" style="width: 30px;text-align: center;">
                                                <input type="text" disabled="disabled" value="<?php
                                                                                                $re = $this->size_model->get_info($items['size']);
                                                                                                echo $re->name;
                                                                                                ?>" style="width: 30px;text-align: center;">
                                                <?php
                                                $input = array();
                                                $input['where'] = array('product_id' => $items['id']);
                                                $input['order'] = array('size_id', 'DESC');
                                                $input['limit'] = array('1', '0');
                                                $size_max = $this->sizedetail_model->get_list($input);
                                                if ($items['size'] < $size_max[0]->size_id) {
                                                ?>
                                                    <a class="cart-sumsub" href="<?php echo base_url('cart/sumsize/' . $items['cartid'] . '/' . $items['id']); ?>">+</a>
                                                <?php } ?>
                                            </td>

                                            <td style="text-align: center"><a style="color: #00FF00" href="<?php echo base_url('cart/newsize/' . $items['id']); ?>"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></a></td>
                                            <td><?php echo number_format($items['subtotal']); ?> VNĐ</td>
                                        </tr>
                                    <?php }
                                    ?>
                                    <tr>
                                        <td colspan="6">Tổng tiền</td>
                                        <td style="font-weight: bold;color:green"><?php echo number_format($total_price); ?> VNĐ</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-4 payment_method">
                            <h4>Phương thức thanh toán</h4>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked>
                                <label class="form-check-label" for="cash">
                                    Tiền mặt
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="vnpay" value="vnpay">
                                <img src="<?php echo base_url('images/vnpay.png') ?>" alt="" height="50" width="64">
                                <label class="form-check-label" for="vnpay">
                                    Vnpay
                                </label>
                            </div>
                            <button type="submit" name="redirect" class="btn btn-danger">Thanh toán ngay</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>