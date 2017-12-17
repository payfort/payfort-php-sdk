<?php include('header.php') ?>
    <section class="nav">
        <ul>
            <li class="lead" > Payment Method</li>
            <li class="lead" > Pay</li>
            <li class="active lead" > Done</li>
        </ul>
    </section>
    <section class="confirmation">
        <label class="success" for="" >Success</label>
        <!-- <label class="failed" for="" >Failed</label> -->
        <small>Thank You For Your Order</small>
    </section>

    <section class="order-confirmation">
        <label for="" class="lead">Order ID : <?php echo $_REQUEST['fort_id']?></label>
    </section>

    <div class="h-seperator"></div>
    
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
    
    <div class="h-seperator"></div>
    
    <section class="actions">
        <a class="btm" href="index.php">New Order</a>
    </section>
<?php include('footer.php') ?>