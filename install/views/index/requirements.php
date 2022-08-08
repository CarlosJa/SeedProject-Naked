<main>

    <section class="container">
        <div class="row py-lg-5">
            <h2>Requirements</h2>

            <table class="table mt-3">
                <thead class="table-dark">
                <th class="bg-gray-200">Prerequisites</th>
                <th class="bg-gray-200">Required</th>
                <th class="bg-gray-200">Current</th>
                <th class="bg-gray-200"></th>
                </thead>
                <tbody>
                <tr>
                    <td>PHP Version</td>
                    <td>7.4+</td>
                    <td><?= PHP_VERSION ?></td>
                    <td>
                        <?php if(version_compare(PHP_VERSION, '7.4.0') >= 0): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                            <? $rFlag = true; ?>

                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td>cURL</td>
                    <td>Enabled</td>
                    <td><?= function_exists('curl_version') ? 'Enabled' : 'Not Enabled' ?></td>
                    <td>
                        <?php if(function_exists('curl_version')): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                            <? $rFlag = true; ?>

                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td>OpenSSL</td>
                    <td>Enabled</td>
                    <td><?= extension_loaded('openssl') ? 'Enabled' : 'Not Enabled' ?></td>
                    <td>
                        <?php if(extension_loaded('openssl')): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                            <? $rFlag = true; ?>

                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td>mbstring</td>
                    <td>Enabled</td>
                    <td><?= extension_loaded('mbstring') && function_exists('mb_get_info') ? 'Enabled' : 'Not Enabled' ?></td>
                    <td>
                        <?php if(extension_loaded('mbstring') && function_exists('mb_get_info')): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                            <? $rFlag = true; ?>

                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td>PDO</td>
                    <td>Enabled</td>
                    <td>
                        <? if (class_exists('PDO', false)) { echo "Enabled"; } ?>
                    </td>
                    <td>
                        <?php if(function_exists('mysqli_connect')): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                            <? $rFlag = true; ?>

                        <?php endif ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="table mt-5">
                <thead class="table-dark">
                <th class="bg-gray-200">Path / File</th>
                <th class="bg-gray-200">Status</th>
                <th class="bg-gray-200"></th>
                </thead>
                <tbody>
                <tr>
                    <td>/config.php</td>
                    <td><?= is_writable( '../config.php') ? 'Writable' : 'Not Writable';?></td>
                    <td>
                        <?php if(is_writable('../config.php')): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                            <? $rFlag = true; ?>

                        <?php endif ?>
                    </td>
                </tr>

                <tr>
                    <td>/uploads/</td>
                    <td><?= is_writable('../uploads/') ? 'Writable' : 'Not Writable' ?></td>
                    <td>
                        <?php if(is_writable('../uploads/')): ?>
                            <i class="bi bi-check2-circle text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-octagon text-danger"></i>
                        <? $rFlag = true; ?>
                        <?php endif ?>
                    </td>
                </tr>

                </tbody>
            </table>

            <div class="mt-3">
                <?php if(!$rFlag): ?>
                    <div class="d-grid gap-2">
                        <hr>
                         Everything looks good. Proceed to the next step.
                        <a href="/install/i/setup" class="btn btn-primary">Next Step</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger" role="alert">
                        Please make sure all the requirements listed on the documentation and on this page are met before continuing!
                    </div>
                    <p class="text-danger"></p>
                <?php endif ?>
            </div>

        </div>
    </section>
</main>


