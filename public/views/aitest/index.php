<?
    $Response = $this->OpenAiResponse;
?>



<main>

    <div class="container mt-5">
        <h2>Testing OpenAI / ChatGPT API</h2>
        <form method="post">
            <div class="form-group">
                <label for="exampleFormControlInput1">Ask Your Question: </label>
                <input type="text" class="form-control" name="question" id="exampleFormControlInput1" placeholder="How is a Rainbow Made?">
            </div>
<!--            <div class="form-group">-->
<!--                <label for="exampleFormControlSelect1">Example select</label>-->
<!--                <select class="form-control" id="exampleFormControlSelect1">-->
<!--                    <option>1</option>-->
<!--                    <option>2</option>-->
<!--                    <option>3</option>-->
<!--                    <option>4</option>-->
<!--                    <option>5</option>-->
<!--                </select>-->
<!--            </div>-->
<!--            <div class="form-group">-->
<!--                <label for="exampleFormControlTextarea1">Example textarea</label>-->
<!--                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>-->
<!--            </div>-->

            <div class="d-grid gap-2 mt-3">

                <input type="submit" value="Submit Question" class="btn btn-primary">
            </div>

        </form>
        <hr class="mt-5">
        <h4 class="mb-3"><strong>Your Question: </strong> <?= $_POST['question']; ?></h4>

        <h4 class="mb-4"><strong>Your Answer:</strong> </h4>
        <pre class="alert alert-light" style="white-space: pre-wrap;">
            <?= $Response; ?>
        </pre>
    </div>

</main>

<footer class="text-muted py-5">
    <div class="container">
        <p class="float-end mb-1">
            <a href="#">Powered by SeedProject</a>
        </p>
        <p class="mb-1">My Application &copy; 2022 </p>
    </div>
</footer>
