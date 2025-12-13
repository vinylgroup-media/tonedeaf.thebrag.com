<div class="observer-sub-form justify-content-center my-3 p-0 d-flex align-items-stretch bg-dark text-white">
    <div class="img-wrap" style="background-image: url(<?php echo $args['image_url']; ?>); background-size: cover;"></div>
    <div class="observer-sub-form-info p-3" style="justify-content: start;">
        <h2 class="h5 mb-0 observer-title">Love <?php echo str_replace('Vinyl ', '', $args['title']); ?>?</h2>
        <p class="observer-desc mb-2">
            <?php echo $args['description']; ?>
        </p>
        <form action="#" method="post" id="observer-subscribe-form<?php echo $args['post_id']; ?>"
              name="observer-subscribe-form"
              class="observer-subscribe-form">
            <p class="d-none js-errors-subscribe"></p>
            <p class="d-none js-msg-subscribe" style="text-align: center; line-height: calc(180px - 4.5rem)"></p>
            <div class="d-flex justify-content-start" style="background: #000; justify-content: space-between; gap: 0.5rem;">
                <input type="hidden" name="list" value="<?php echo $args['topic_id']; ?>">
                <input type="email" name="email" class="observer-sub-email" placeholder="Your email" value="" required />
                <div class="d-flex submit-wrap">
                    <input type="submit" value="Join" name="subscribe" class="button btn btn-join btn-primary" style="color: #fff !important">
                </div>
            </div>
        </form>
    </div>
    <div class="spinner d-none">
        <img src="https://images-r2-2.thebrag.com/common/spinner.gif" width="30" height="30" alt="">
    </div>
</div>
