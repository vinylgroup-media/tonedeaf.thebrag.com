<?php /* Template Name: Quiz */ ?>
<?php get_template_part( 'header-quiz' );
wp_enqueue_script( 'quiz', get_template_directory_uri() . '/js/quiz.js', array ( 'jquery' ), '1', true);
?>
<link rel="stylesheet" id="quiz-poll-css" href="<?php echo get_template_directory_uri(); ?>/css/quiz-poll.css" type="text/css" media="all" />
<div class="quiz-story<?php echo $_POST ? ' result' : ''; ?>">
    <?php $featured_img_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'cover-story' ); ?>
    <img src="<?php echo $featured_img_src[0]; ?>" style="display: block; margin: auto;">
    <?php
    $questions = CFS()->get( 'questions');
    
    $results = array();
    $results_loop = CFS()->get('results_loop');
    if ( count( $results_loop ) > 0 ) :
        foreach ( $results_loop as $result ) :
            $scores = explode( '-', $result['score'] );
            if ( count( $scores ) > 1 ) :
                for( $s = $scores[0]; $s <= $scores[1]; $s++ ) :
                    $results[ $s ] = $result['result_text'];
                endfor;
            else:
                $results[ $result['score'] ] = $result['result_text'];
            endif;
        endforeach;
    endif;
    
    $correct_answers = array();
    if ( $questions ) :
        $i = 0;
        foreach ( $questions as $question ) :
            $correct_answers[$i]['image'] = $question['image'];
            $correct_answers[$i]['answer_image'] = $question['answer_image'];
            $correct_answers[$i]['full_audio'] = $question['full_audio'];
            $correct_answers[$i]['answer_description'] = $question['answer_description'];
            $correct_answers[$i]['spotify_uri'] = $question['spotify_uri'];
            
            if ( $question['answers'] ) :
                foreach ( $question['answers'] as $answer ) :
                    if ( $answer['correct_answer'] ) :
                        $correct_answers[$i]['answer_text'] = $answer['answer_text'];
//                        $correct_answers[$i]['answer_description'] = $answer['answer_description'];
                        break;
                    endif;
                endforeach;
            endif;
            $i++;
        endforeach;
    endif;
    if ( $_POST ) :
        
        if ( isset( $_POST['email'] ) && trim( $_POST['email'] ) != '' ) :
            $email = $_POST['email'];
            $mc = ssm_mailchimp_subscriber_status( $email, 'subscribed', 'b6f823df63', '727643e6b14470301125c15a490425a8-us1' );
        endif;
        
        // Show score and result info
        $answers = $_POST['answers'];
        $score = 0;
        if ( count( $answers ) > 0 ) :
            foreach ( $answers as $i => $answer ) :
                if ( $answer == $correct_answers[$i]['answer_text'] ) :
                    $score++;
                endif;
            endforeach;
        endif;
        $result_per = $score / count( $correct_answers ) * 100;
?>
        <div class="intro-outro">
            <h1 class="story-title">You got: <?php echo $score; ?>/<?php echo count( $correct_answers ); ?></h1>
            <p><?php echo $results[ $result_per ]; ?></p>
            <p>Want to test your mates on their music knowledge? Click below to share:</p>
            <?php // do_action("mashshare"); ?>
            <?php do_action( 'ssm_social_sharing_buttons', 'row' ); ?>
            <p><?php echo CFS()->get('intro_2'); ?></p>
        </div>
        <section class="quiz-section">
            <?php foreach ( $correct_answers as $i => $correct_answer ) : $answer_img_src = wp_get_attachment_image_src( $correct_answer['answer_image'], 'medium_large' ); ?><div class="quiz result">
                <div class="quiz-img clickable<?php echo in_array( $correct_answer['answer_text'], $answers ) ? ' correct' : ''; ?>" data-qz="<?php echo $i; ?>" style="background-image: url(<?php echo $answer_img_src[0]; ?>)"></div>
                <h2 class="clickable<?php echo in_array( $correct_answer['answer_text'], $answers ) ? ' correct' : ''; ?>" data-qz="<?php echo $i; ?>"><?php echo $correct_answers[$i]['answer_text']; ?></h2>
                <div class="quiz-details result" id="quiz-details-<?php echo $i; ?>" data-qz="<?php echo $i; ?>">
                    <div class="answer-img" style="background-image: url(<?php echo $answer_img_src[0]; ?>)"></div>
                    <div class="answer-details">
                        <div style="padding: 0 0 20px 20px;">
                            <h2><?php echo $correct_answer['answer_text']; ?></h2>
                            <p><?php echo $correct_answer['answer_description']; ?></p>
                            <?php if ( $correct_answer['spotify_uri'] ) : $spotify_base_url = 'https://open.spotify.com/embed?uri='; ?>
                            <iframe src="<?php echo $spotify_base_url . $correct_answer['spotify_uri']; ?>&theme=black" width="100%" height="80" frameborder="0" allowtransparency="true"></iframe>
                            <!--<div class="quiz-audio"><?php // echo wp_audio_shortcode( array( 'src' => $correct_answer['full_audio'] ) ); ?></div>-->
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div><?php endforeach; ?>
        </section>
        <div class="clear"></div>
        <div class="intro-outro"><p><?php echo CFS()->get('outro_2'); ?></p></div>
        <div class="clear"></div>
        <div class="related-articles">
        <?php
        $related_articles = CFS()->get('related_articles');
        if ( count( $related_articles ) > 0 ):
        ?>
            <h1>Find out more:</h1>
        <?php
            foreach ( $related_articles as $related_article ) :
                $article = get_post( $related_article );
        ?>
            <div class="article-wrap">
                <div class="article">
                    <?php $img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $article->ID ), 'medium_large' ); ?>
                    <a href="<?php echo get_the_permalink( $article->ID ); ?>" target="_blank">
                        <div class="img" style="background-image: url(<?php echo $img_src[0]; ?>)"></div>
                    </a>
                    <h2 class="title"><a href="<?php echo get_the_permalink( $article->ID ); ?>" target="_blank"><?php echo $article->post_title; ?></a></h2>
                    <p class="excerpt"><?php
                    $metadesc = get_post_meta( $article->ID, '_yoast_wpseo_metadesc', true );
                    $excerpt = trim( $metadesc ) != '' ? $metadesc : string_limit_words( get_the_excerpt( $article->ID ), 25 );
                    echo $excerpt;
                    ?></p>
                </div>
            </div>
        <?php
            endforeach;
        endif;
        ?>
        </div>
        <div class="clear"></div>
<?php else: // Show Quiz ?>
        <div class="intro-outro" style="margin-top: 20px;">
            <!--<h1 class="story-title"><?php the_title(); ?></h1>-->
            <?php if ( CFS()->get('heading_2') ) : ?>
            <h2 style="text-align: center; line-height: 2.5rem;"><?php echo CFS()->get('heading_2'); ?></h2>
            <?php endif; ?>
            <p><?php echo CFS()->get('intro_1'); ?></p>
        </div>
    <?php
    if ( $questions ) :
    ?>
    <form action="" method="post">
    <section class="quiz-section"><?php $i = 0; foreach ( $questions as $question ) : $img_src = wp_get_attachment_image_src( $question['image'], 'medium_large' );?><div class="quiz result">
            <div class="quiz-img clickable" data-qz="<?php echo $i; ?>" style="background-image: url(<?php echo $img_src[0]; ?>)"></div>
            <div class="quiz-details" id="quiz-details-<?php echo $i; ?>" data-qz="<?php echo $i; ?>">
                <div class="quiz-audio"><?php echo wp_audio_shortcode( array( 'src' => $question['snippet'] ) ); ?></div>
                <div class="quiz-questions">
                    <?php if ( $question['answers'] ) : shuffle( $question['answers'] ); foreach ( $question['answers'] as $j => $answer ) : ?>
                    <div style="clear: both;">
                    <input type="radio" class="radio answer_choice" name="answers[<?php echo $i; ?>]" id="answer_<?php echo $i . '_' . $j; ?>" value="<?php echo $answer['answer_text']; ?>">
                    <label for="answer_<?php echo $i . '_' . $j; ?>"><?php echo $answer['answer_text']; ?></label>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div><?php $i++; endforeach; // For Each $question in $questions ?></section>
        <div class="intro-outro"><p><?php echo CFS()->get('outro_1'); ?></p></div>
        <!--<input type="email" name="email" placeholder="Your Email Address" style="width: 100%; padding: 10px; box-sizing: border-box">-->
        <input type="submit" name="submit" value="Submit" class="button" id="btn-submit">
    </form>
    <?php endif; // If there are questions ?>
<?php endif; // If $_POST is set ?>
</div>
<?php get_footer();