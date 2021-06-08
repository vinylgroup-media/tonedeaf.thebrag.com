<?php /* Template Name: Quiz (Rekorderlig #2) */ ?>
<?php get_template_part( 'header-quiz-rekorderlig' );
$page_url = (get_permalink());
$page_title = get_the_title();
wp_enqueue_script( 'quiz-rekorderlig', get_template_directory_uri() . '/js/quiz-rekorderlig2.js', array ( 'jquery' ), '20181109-2', true);
$args = array(
    'url'   => admin_url( 'admin-ajax.php' ),
    'page_url' => $page_url,
    'page_title' => $page_title,
);
wp_localize_script( 'quiz-rekorderlig', 'quiz_rekorderlig', $args );
?>

<?php if ( ! post_password_required() ) : ?>

<div class="quiz-story<?php echo isset( $_POST ) ? ' result' : ''; ?>">
<?php
// My new favourite Rekorderlig Sauna Sound artist is 
    $results = array(
        0 => array(
            'title' => 'Sunscreen',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_Q2_results0.jpg',
        ),
        1 => array(
            'title' => 'B Wise',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_Q2_results1.jpg',
        ),
        2 => array(
            'title' => 'Nick Nuisance and The Delinquents',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_Q2_results2.jpg',
        ),
        3 => array(
            'title' => 'CLYPSO',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_Q2_results3.jpg',
        ),
        4 => array(
            'title' => 'Spike Vincent',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_Q2_results4.jpg',
        ),
        5 => array(
            'title' => 'Milan Ring',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_Q2_results5.jpg',
        ),
    );
    
    $questions = array(
        array(
            'question_no' => 1,
            'question' => 'Where are you most likely to be found on a Sunday Morning?',
            'answers' => array(
                array( 'text' => 'At a Bunnings sausage sizzle', 'result' => 2, ),
                array( 'text' => 'Walking your dog in Camperdown Park', 'result' => 0, ),
                array( 'text' => 'Hitting the studio, making beats', 'result' => 1, ),
                array( 'text' => 'Heading to brunch with friends', 'result' => 5, ),
                array( 'text' => 'Doing YOGA by the beach', 'result' => 3, ),
                array( 'text' => 'Hanging out with mates', 'result' => 4, ),
            ),
        ),
        array(
            'question_no' => 2,
            'question' => 'Which festival would you most like to go to?',
            'answers' => array(
                array( 'text' => 'Listen Out', 'result' => 1, ),
                array( 'text' => 'Field Day', 'result' => 3, ),
                array( 'text' => 'Splendour In The Grass', 'result' => 4, ),
                array( 'text' => 'Yours &amp; Owls', 'result' => 0, ),
                array( 'text' => 'Falls Festival', 'result' => 2, ),
                array( 'text' => 'Beyond The Valley', 'result' => 5, ),
            ),
        ),
        array(
            'question_no' => 3,
            'question' => 'What\'s your dream city to live in?',
            'answers' => array(
                array( 'text' => 'Wollongong', 'result' => 2, ),
                array( 'text' => 'Byron Bay', 'result' => 0, ),
                array( 'text' => 'New York City', 'result' => 5, ),
                array( 'text' => 'Los Angeles', 'result' => 1, ),
                array( 'text' => 'Amsterdam', 'result' => 3, ),
                array( 'text' => 'Melbourne', 'result' => 4, ),
            ),
        ),
        array(
            'question_no' => 4,
            'question' => 'Where are you most likely to be found at a gig?',
            'answers' => array(
                array( 'text' => 'Up the front, singing along to every word', 'result' => 1, ),
                array( 'text' => 'Getting rowdy in the mosh pit', 'result' => 2, ),
                array( 'text' => 'Vibing somewhere in the middle', 'result' => 0, ),
                array( 'text' => 'Hitting the bar and chatting', 'result' => 5, ),
                array( 'text' => 'Side stage for your mate’s band', 'result' => 4, ),
                array( 'text' => 'Watching your favourite producer in total awe', 'result' => 3, ),
            ),
        ),
        array(
            'question_no' => 5,
            'question' => 'What\'s your ideal sneaker?',
            'answers' => array(
                array( 'text' => 'An old pair of Vans', 'result' => 2, ),
                array( 'text' => 'Converse One Stars', 'result' => 1, ),
                array( 'text' => 'Ironic Nike TN\'s', 'result' => 4, ),
                array( 'text' => 'Adidas Stan Smiths', 'result' => 5, ),
                array( 'text' => 'Sneakers? Birkenstocks, please', 'result' => 0, ),
                array( 'text' => 'Nike Air Force One\'s', 'result' => 3, ),
            ),
        ),
        array(
            'question_no' => 6,
            'question' => 'What\'s your favourite sauce?',
            'answers' => array(
                array( 'text' => 'Tomato', 'result' => 2, ),
                array( 'text' => 'Sriracha', 'result' => 0, ),
                array( 'text' => 'Gravy', 'result' => 4, ),
                array( 'text' => 'Tabasco', 'result' => 5, ),
                array( 'text' => 'Mustard', 'result' => 1, ),
                array( 'text' => 'Sweet Chilli', 'result' => 3, ),
            ),
        ),
        array(
            'question_no' => 7,
            'question' => 'What\'s your all time favourite genre?',
            'answers' => array(
                array( 'text' => 'Hip hop', 'result' => 1, ),
                array( 'text' => 'Electronic', 'result' => 3, ),
                array( 'text' => 'Garage Rock', 'result' => 2, ),
                array( 'text' => 'Post-punk', 'result' => 4, ),
                array( 'text' => 'Indie rock', 'result' => 0, ),
                array( 'text' => 'RnB', 'result' => 5, ),
            ),
        ),
    );

//    for( $i = 1; $i < 10; $i++ ) unset( $questions[$i] );
    
    shuffle( $questions );
    ?>
<script>
    var total_questions = <?php echo count( $questions ); ?>;
    var questions = [];
    <?php foreach ( $questions as $question ) : ?>
    questions[<?php echo $question['question_no']; ?>] = '<?php echo $question['correct_answer']; ?>';
    <?php endforeach; ?>
        
    var results= [];
    <?php foreach ( $results as $key =>  $result ) : ?>
    results[<?php echo $key; ?>] = <?php echo json_encode( $result ); ?>;
    <?php endforeach; ?>
</script>

    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="form-quiz">
    <section class="quiz-section">
        <?php if ( $questions ) : ?>
        <div class="quiz-header" style="cursor: pointer;"><img src="<?php echo get_template_directory_uri(); ?>/images/quiz-rekorderlig/Rekorderling_quiz2-Header.jpg"></div>
        <div class="questions-wrap" id="questions-wrap">
            <div>
                <?php the_content(); ?>
            </div>
            <div class="quiz-details-wrap">
        <?php $i = 0; foreach ( $questions as $question ) : ?>
            <div class="quiz-details" id="quiz-details-<?php echo $i; ?>" data-qz="<?php echo $i; ?>" data-qn="<?php echo $question['question_no']; ?>" style="<?php echo $i > 0 ? 'display: none;' : ''; ?>">
                <div class="quiz-question">
                    <div class="question-number">
                        <span><?php echo $i + 1; ?></span>
                    </div>
                    <div class="question-text">
                        <span><?php echo $question['question']; ?></span>
                    </div>
                </div>
                
                <div class="quiz-answers">
                    <?php
                    if ( $question['answers'] ) :
                        $question['answers'] = shuffle_assoc( $question['answers'] );
                        $j = 0;
                        foreach ( $question['answers'] as $answer ) :
                    ?>
                    <div class="quiz-answer">
                        <input type="radio" class="radio answer_choice<?php echo $i == 0 ? ' answer_choice-rekorderlig-start' : ''; echo ( $i + 1 ) == count( $questions ) ? ' answer_choice-rekorderlig-last': ''; ?>" name="answers[<?php echo $question['question_no']; ?>]" id="answer_<?php echo $i . '_' . $j; ?>" value="<?php echo $answer['result']; ?>">
                        <label for="answer_<?php echo $i . '_' . $j; ?>" class="answer_choice_label answer_choice_label-rekorderlig"><span><?php echo $answer['text']; ?></span></label>
                    </div>
                    <?php $j++; 
                        endforeach; // For Each Answer of Question
                    endif; ?>
                </div>
                <div class="clear"></div>
            </div>
        <?php $i++; endforeach; // For Each $question in $questions ?>
            <div class="clear"></div>

            </div>

            <div id="results" style="display: none;">
                <div class="results-wrap" style="position: relative;">
                <div class="result-images" style="line-height: 0">
                    <?php foreach( $results as $key => $result ): ?>
                        <div class="result-image" id="result-image<?php echo $key; ?>" style="<?php echo $key == 0 ? 'display: block;' : ''; ?>">
                            <img src="<?php echo $result['img']; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="position: absolute; bottom: 0%; right: 6.7%; width: 22%; " class="share-icons">
                    
                <?php
                $page_url = urlencode(get_permalink());
                $page_title = str_replace( ' ', '%20', get_the_title());
                
                $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u=' . $page_url;
                $twitterURL = 'https://twitter.com/intent/tweet?text=' . $page_title . '&amp;url=' . $page_url;

                $content = '<div class="social-share-buttons">';
                $content .= '<a class="social-share-link social-share-quiz-facebook" id="social-share-quiz-facebook" href="' . $facebookURL . '" target="_blank" data-type="share-fb" style="width: 50%;"><img src="' . get_template_directory_uri() . '/images/quiz-rekorderlig/FacebookCircle_rekorderlig.png"></a>';
                $content .= '<a class="social-share-link social-share-quiz-twitter" id="social-share-quiz-twitter" href="' . $twitterURL . '" target="_blank" data-type="share-twitter" style="width: 50%;"><img src="' . get_template_directory_uri() . '/images/quiz-rekorderlig/TwitterCircle_rekorderlig.png"></a>';
                $content .= '</div>';
                echo $content;
                ?>
                
                </div>
                </div>
            </div>
            
            <div style="padding: 10px;">
                <div class="progress-dots">
                    <?php for( $i = 0; $i < count( $questions ); $i++ ): ?>
                    <div class="dot" id="dot-<?php echo $i; ?>"></div>
                    <?php if ( $i < count( $questions )- 1 ) : ?>
                    <div class="line">-</div>
                    <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div>
            
        </div>
        </div>
        <?php endif; // If there are questions ?>
    </section>
    </form>
    
    

</div>

<?php else: ?>
<div class="quiz-story">
    <form method="post" action="/wp-login.php?action=postpass">
    <p>This content is password protected. To view it please enter your password below:</p>
    <p>Password:<br/>
    <input type="password" size="20" id="pwbox-<?php echo get_the_ID(); ?>" name="post_password"/><br/>
    <input type="submit" value="Enter" name="Submit"/></p>
    </form>
</div>
<?php endif; ?>

<?php get_template_part( 'footer-quiz-rekorderlig' );

function shuffle_assoc($list) { 
    if (!is_array($list)) return $list; 
    $keys = array_keys($list); 
    shuffle($keys); 
    $random = array(); 
    foreach ($keys as $key) 
        $random[$key] = $list[$key]; 
    return $random; 
}