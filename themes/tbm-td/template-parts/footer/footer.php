<footer class="container footer mb-1 p-2 p-md-5">
    <div class="d-flex justify-content-between flex-column flex-md-row px-0 px-md-2 py-3 mt-3">
        <div class="left px-3">
            <a href="<?php echo site_url(); ?>"><img src="<?php echo CDN_URL; ?>Tone-Deaf-light.svg" width="100" height="55" alt="Tone Deaf" title="Tone Deaf" loading="lazy"></a>
        </div>
        <div class="right px-3 text-right d-flex mt-3 mt-md-0 justify-content-end">
            <span class="mr-1 text-color-primary font-primary" style="font-size: .9rem; font-weight: 500">MUSIC BY</span>
            <span><img src="https://cdn.thebrag.com/tbm/The-Brag-Media-300px-light.png" width="130" height="13" alt="The Brag Media" title="The Brag Media" loading="lazy"></span>
        </div>
    </div>
    <div class="footer-menu pt-5 border-top mt-5 px-2">
        <div class="d-flex flex-column flex-md-row align-items-start">
            <div class="left px-2 px-md-3">
                <nav>
                    <ul>
                        <li><a href="https://thebrag.media/" target="_blank" rel="noreferrer">Advertise</a></li>
                        <li><a target="_blank" rel="noopener" href="https://thebrag.media/submit-a-tip/">Submit Tip</a></li>
                        <li><a target="_blank" rel="noopener" href="https://thebrag.media/how-to-submit-an-op-ed-essay/">Submit Op-Ed</a></li>
                        <li><a target="_blank" rel="noopener" href="https://thebrag.media/submit/">Submit Video</a></li>
                    </ul>
                </nav>
            </div>
            <div class="right mt-3 mt-md-0">
                <?php get_template_part('template-parts/observer-list', null, ['show_container' => true, 'container_id' => 'observer-list-footer']); ?>
            </div>
        </div>
    </div>
    <div class="container footer-menu-2 pb-0">
        <nav class="mx-2">
            <ul class="d-flex flex-column flex-md-row justify-content-center p-0 mb-0">
                <li><a href="https://thebrag.media/terms-and-conditions/" target="_blank" rel="noreferrer" class="py-1">Competition Ts &amp; Cs</a></li>
                <li><a href="https://thebrag.com/media/editorial-code/" target="_blank" rel="noreferrer" class="py-1">Editorial code</a></li>
                <li><a href="https://thebrag.com/media/terms-of-use/" target="_blank" rel="noreferrer" class="py-1">Terms of use</a></li>
                <li><a href="https://thebrag.media/privacy-policy/" target="_blank" rel="noreferrer" class="py-1">Privacy</a></li>
            </ul>
        </nav>
        <a href="https://vinyl.group/" target="_blank" class="d-flex" style="justify-content: center; align-items: center; padding-top: 1rem; padding-bottom: 1rem;"">
            <img src="<?php echo IMAGES_R2_CDN_URL; ?>common/brands/202309/a-vinyl-group-company-white.png" style="height: 2rem;" alt="brag logo">
        </a>
    </div>
</footer>