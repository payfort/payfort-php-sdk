<?php include('header.php') ?>

            <section class="nav">
                <ul>
                    <li class="lead" > Payment Method</li>
                    <li class="active lead" > Done</li>
                </ul>
            </section>
            <section class="confirmation">
                <label class="failed" for="" >Payment Failed</label>
                <!-- <label class="failed" for="" >Failed</label> -->
                <small>Error while processing your payment</small>
            </section>
            
            <div class="h-seperator"></div>
            
            <?php if(isset($_REQUEST['error_msg'])) : ?>
            <section>
                <div class="error">error message : error_msg</div>
            </section>
            <div class="h-seperator"></div>
            

            <div class="h-seperator"></div>

            <section class="actions">
                <a class="btm" href="index.php">New Order</a>
            </section>
			<?php endif; ?>
<?php include('footer.php') ?>
