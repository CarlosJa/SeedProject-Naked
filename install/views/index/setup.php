<main>

    <section class="container">
        <div class="row py-lg-5">
            <h2>Setup</h2>

            <form action="/install/index/installation" name="setup" method="post">
                <div class="mb-3 mt-5">
                    <label for="exampleFormControlInput1" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
                </div>
                <div class="mb-5">
                    <label for="exampleFormControlInput1" class="form-label">Website Url</label>
                    <input type="text" name="website" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
                    <div id="emailHelp" class="form-text">Make sure to specify the full url of the installation path of the website. <code>https://www.yourproject.com/ </code></div>
                </div>
                <hr class="mb-5">
                <h3 class="mt-3">Database Info</h3>
                <div class="mb-3 mt-5">
                    <label for="exampleFormControlInput1" class="form-label">Localhost</label>
                    <input type="text" name="dbloca" class="form-control" id="dblocal" placeholder="localhost" value="localhost">
                    <div id="emailHelp" class="form-text">Make sure to specify the full url of the installation path of the website. <code>https://www.yourproject.com/ </code></div>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Databse Username</label>
                    <input type="text" name="dbuser" class="form-control" id="dbuser" placeholder="" value="">
                    <div id="emailHelp" class="form-text">Make sure to specify the full url of the installation path of the website. <code>https://www.yourproject.com/ </code></div>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Databse Password</label>
                    <input type="text" name="dbpass" class="form-control" id="dbpass" placeholder="" value="">
                    <div id="emailHelp" class="form-text">Make sure to specify the full url of the installation path of the website. <code>https://www.yourproject.com/ </code></div>
                </div>

                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Databse Name</label>
                    <input type="text" name="dbname" class="form-control" id="dbname" placeholder="" value="">
                    <div id="emailHelp" class="form-text">Make sure to specify the full url of the installation path of the website. <code>https://www.yourproject.com/ </code></div>
                </div>

                <div class="mt-5">
                    <div id="dbstatus" class="alert" style="display: none"></div>
                    <input type="submit" class="btn btn-primary" value="Complete Installation">
                    <button class="btn btn-secondary" id="checkDB" type="button">Test Database Connection</button>
                </div>

            </form>

        </div>
    </section>
</main>


