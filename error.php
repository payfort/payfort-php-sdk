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
                <div class="error"><?php echo $_REQUEST['error_msg']?></div>
            </section>
            <div class="h-seperator"></div>
            <?php endif; ?>

            <?php if(isset($_REQUEST['merchant_reference'])): ?>
            <section class="details">
                <h3>Response Details</h3>
                <br/>
                <table>
                    <tr>
                        <th>
                            Parameter Name
                        </th>
                        <th>
                            Parameter Value
                        </th>
                    </tr>
                <?php
                   foreach($_REQUEST as $k => $v) {
                       echo "<tr>";
                       echo "<td>$k</td><td>$v</td>";
                       echo "</tr>";
                   } 
                ?>
                </table>
            </section>
            <?php endif; ?>

            <div class="h-seperator"></div>

            <section class="actions">
                <a class="btm" href="index.php">New Order</a>
            </section>
<?php include('footer.php') ?>