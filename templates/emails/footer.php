<?php

/**
 * Footer Email Template - Thiên Tâm 68
 */

$company_name = 'Công ty Cổ Phần Dịch Vụ Thiên Tâm';
$hotline      = '0935 425 238';
$email        = 'thientamdl68@gmail.com';
$address      = 'Số 61/2 An Bình, P. Xuân Hương, TP. Đà Lạt, Tỉnh Lâm Đồng';
$site_url     = 'https://thientam68.com';
?>
                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td class="email-footer-pad" style="background-color: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 25px 32px; text-align: center;">
                            <p style="margin: 0 0 10px; font-size: 13px; font-weight: 700; color: #0D2A54;">
                                <?php echo esc_html($company_name); ?>
                            </p>
                            <p style="margin: 0 0 8px; font-size: 12px; color: #64748B; line-height: 1.6;">
                                Địa chỉ: <?php echo esc_html($address); ?><br>
                                Hotline: <a href="tel:<?php echo esc_attr(str_replace(' ', '', $hotline)); ?>" style="color: #174C97; font-weight: bold; text-decoration: none;"><?php echo esc_html($hotline); ?></a>
                                &nbsp;|&nbsp;
                                Email: <a href="mailto:<?php echo esc_attr($email); ?>" style="color: #174C97; text-decoration: none;"><?php echo esc_html($email); ?></a>
                            </p>
                            <p style="margin: 12px 0 0; font-size: 11px; color: #94A3B8;">
                                Website: <a href="<?php echo esc_url($site_url); ?>" target="_blank" style="color: #D4AF37; font-weight: bold; text-decoration: underline;">thientam68.com</a> - Khai tâm kiến tạo, đắc lộc an gia.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
