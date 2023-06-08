<?php
$menuList = [
    '/credit_card_redirect.php'                 => 'Credit Card Redirect',
    '/credit_card_standard.php'                 => 'Credit Card Standard',
    '/credit_card_custom.php'                   => 'Credit Card Custom',
    '/installments_credit_card_redirect.php'    => 'Instalments Redirect',
    '/installments_credit_card_standard.php'    => 'Instalments Standard',
    '/installments_credit_card_custom.php'      => 'Instalments Custom',
    '/credit_card_trusted.php'                  => 'Trusted',
    '/apple_pay.php'                            => 'Apple Pay',
    '/maintenance.php'                          => 'Maintenance operations',
];
?>
<div class="sample_app_menu_container">
    <ul>
        <li class="logo">
           <a href="/">APS PHP SDK</a>
        </li>
        <?php foreach ($menuList as $linkFile => $menuName) {
            $isCurrent = ($activeFile ?? null) === $linkFile;
            ?>
            <li class="<?php if ($isCurrent) echo 'active'; ?>">
                <a href="<?php echo $linkFile; ?>"><?php echo $menuName; ?></a>
            </li>
            <?php
        } ?>
    </ul>
</div>