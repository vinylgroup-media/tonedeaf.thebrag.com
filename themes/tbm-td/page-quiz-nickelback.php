<?php /* Template Name: Quiz (Nickelback) */ ?>
<?php get_template_part( 'header-quiz-nickelback' );
$page_url = (get_permalink());
$page_title = get_the_title();
wp_enqueue_script( 'quiz-nickelback', get_template_directory_uri() . '/js/quiz-nickelback.js', array ( 'jquery' ), '20181213-2', true);
$args = array(
    'url'   => admin_url( 'admin-ajax.php' ),
    'page_url' => $page_url,
    'page_title' => $page_title,
);
wp_localize_script( 'quiz-nickelback', 'quiz_nickelback', $args );
?>

<?php if ( ! post_password_required() ) : ?>

<div class="quiz-story<?php echo isset( $_POST ) ? ' result' : ''; ?>">
<?php
    $results = array(
        array(
            'title' => 'Head back to rock school...it is a long way to the top!',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_0.png',
        ),
        array(
            'title' => 'Head back to rock school...it is a long way to the top!',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_10.png',
        ),
        array(
            'title' => 'Head back to rock school...it is a long way to the top!',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_20.png',
        ),
        array(
            'title' => 'Hopefully today is not your last day, you need to do some homework!',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_30.png',
        ),
        array(
            'title' => 'Hopefully today is not your last day, you need to do some homework!',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_40.png',
        ),
        array(
            'title' => 'You are still a little Far Away...but keep going',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_50.png',
        ),
        array(
            'title' => 'You are still a little Far Away...but keep going',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_60.png',
        ),
        array(
            'title' => 'You are a Rockstar - you probably do a mean karaoke cover',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_70.png',
        ),
        array(
            'title' => 'You are a Rockstar - you probably do a mean karaoke cover',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_80.png',
        ),
        array(
            'title' => 'The hall of fame is calling your name',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_90.png',
        ),
        array(
            'title' => 'The hall of fame is calling your name',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-nickelback/Results_100.png',
        )
    );
    
    $questions = array(
        array(
            'question_no' => 1,
            'question' => 'Which band were originally known as the Village Idiots',
            'answers' => array(
                'Silverchair',
                'blink-182',
                'Nickelback',
                'Green Day',
            ),
            'correct_answer' => 'Nickelback'
        ),
        array(
            'question_no' => 2,
            'question' => 'In the 2000s, which act was only second to the Beatles for being the best-selling foreign act in the US',
            'answers' => array(
                'The Killers',
                'Oasis',
                'Nickelback',
                'Radiohead',
            ),
            'correct_answer' => 'Nickelback'
        ),
        
        array(
            'question_no' => 3,
            'question' => 'Canada has produced a slew of massive artists - which band originates from Hanna, Alberta?',
            'answers' => array(
                'Neil Young &amp; Crazy Horse ',
                'Sum 41',
                'Nickelback',
                'Simple Plan',
            ),
            'correct_answer' => 'Nickelback'
        ),
        array(
            'question_no' => 4,
            'question' => 'Since 1995, which band have played 11 completely sold out tours?',
            'answers' => array(
                'Coldplay',
                'U2',
                'Nickelback',
                'Slipknot',
            ),
            'correct_answer' => 'Nickelback'
        ),
        array(
            'question_no' => 5,
            'question' => 'How many spins did the most played song on US radio in the 2000s recieve?',
            'answers' => array(
                '1.2 million',
                '756 thousand',
                'Less than 10 thousand',
                '5 million',
            ),
            'correct_answer' => '1.2 million'
        ),
        array(
            'question_no' => 6,
            'question' => 'What song do you think it was?',
            'answers' => array(
                'How You Remind Me',
                'Californication',
                'Beautiful Day',
                'Yellow',
            ),
            'correct_answer' => 'How You Remind Me'
        ),
        array(
            'question_no' => 7,
            'question' => 'Which of these bands have sold over 50 million albums worldwide?',
            'answers' => array(
                'Nickelback',
                'The Killers',
                'Slipknot',
                'Korn',
            ),
            'correct_answer' => 'Nickelback'
        ),
        array(
            'question_no' => 8,
            'question' => 'Who do you think is the 11th best selling act in history?',
            'answers' => array(
                'Nirvana',
                'Limp Bizkit',
                'Coldplay',
                'Nickelback',
            ),
            'correct_answer' => 'Nickelback'
        ),
        array(
            'question_no' => 9,
            'question' => 'Which four albums, released after 2005 have reached a Diamond status',
            'answers' => array(
                'Nickelback\'s All the Right Reasons,  Adele\'s "25" and "21" and Garth Brooks\' "The Ultimate Hits"',
                'Adele\'s "25" and "21", Taylor Swift\'s 1989, Bruno Mars "Doo Wops and Hooligans"',
                'Coldplay\'s "X&amp;Y", Nickelback\'s All the Right Reasons,  Adele\'s "25" and "21"',
                'Coldplay\'s "X&amp;Y", Adele\'s "25" and "21"',
            ),
            'correct_answer' => 'Nickelback\'s All the Right Reasons,  Adele\'s "25" and "21" and Garth Brooks\' "The Ultimate Hits"',
        ),
        array(
            'question_no' => 10,
            'question' => 'Which artist has achieved 5 number one studio albums in Canada?',
            'answers' => array(
                'Sum 41',
                'Simple Plan',
                'Nickelback',
                'blink-182',
            ),
            'correct_answer' => 'Nickelback'
        ),
    );

//    for( $i = 1; $i < 10; $i++ ) unset( $questions[$i] );
    
//    shuffle( $questions );
    ?>
<script>
//    jQuery(document).ready(function($) {
        var total_questions = <?php echo count( $questions ); ?>;
        var questions = [];
    
    
        <?php foreach ( $questions as $question ) : ?>
        questions[<?php echo $question['question_no']; ?>] = '<?php echo htmlentities( $question['correct_answer'], ENT_QUOTES ); ?>';
        <?php endforeach; ?>

        var results= [];
        <?php foreach ( $results as $key =>  $result ) : ?>
        results[<?php echo $key; ?>] = <?php echo json_encode( $result ); ?>;
        <?php endforeach; ?>
        
//        });
</script>

    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="form-quiz">
    <section class="quiz-section">
        <?php if ( $questions ) : ?>
        <div class="quiz-header" style="cursor: pointer;"><img src="<?php echo get_template_directory_uri(); ?>/images/quiz-nickelback/Worldsbiggestbands-600px.jpg"></div>
        <div class="questions-wrap" id="questions-wrap">
            <div>
                <h3>How well do you know the world’s biggest bands of the 2000s?</h3>
                <p>The naughties were an incredible time for rock music - with bands like Coldplay, Green Day, The Killers and Nickelback at the helm, records were smashed, tours sold out and albums were devoured at a rapid pace.</p>
                <p>Test your knowledge on the era with this quiz - you may find that one band clearly reigned supreme...</p>
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
                        foreach ( $question['answers'] as $answer_text ) :
                    ?>
                    <div class="quiz-answer">
                        <input type="radio" class="radio answer_choice<?php echo $i == 0 ? ' answer_choice-nickelback-start' : ''; echo ( $i + 1 ) == count( $questions ) ? ' answer_choice-nickelback-last': ''; ?>" name="answers[<?php echo $question['question_no']; ?>]" id="answer_<?php echo $i . '_' . $j; ?>" value="<?php echo $answer_text; ?>">
                        <label for="answer_<?php echo $i . '_' . $j; ?>" class="answer_choice_label answer_choice_label-nickelback"><span><?php echo $answer_text; ?></span></label>
                        <div class="quiz-answer-result <?php echo $answer_text == $question['correct_answer'] ? 'quiz-answer-result-correct' : 'quiz-answer-result-incorrect'; ?>"></div>
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
                
                <div class="share-icons" style="padding-top: 15px; background: #dee3e4; line-height: 1rem">
                    
                <?php
                $page_url = urlencode(get_permalink());
                $page_title = str_replace( ' ', '%20', get_the_title());
                
                $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u=' . $page_url;
                $twitterURL = 'https://twitter.com/intent/tweet?text=' . $page_title . '&amp;url=' . $page_url;

                $content = '<div class="social-share-buttons">';
                $content .= '<a class="social-share-link social-share-quiz-facebook" id="social-share-quiz-facebook" href="' . $facebookURL . '" target="_blank" data-type="share-fb" style="width: 50%;"><img src="' . get_template_directory_uri() . '/images/quiz-nickelback/FacebookCircle.png"></a>';
                $content .= '<a class="social-share-link social-share-quiz-twitter" id="social-share-quiz-twitter" href="' . $twitterURL . '" target="_blank" data-type="share-twitter" style="width: 50%;"><img src="' . get_template_directory_uri() . '/images/quiz-nickelback/TwitterCircle.png"></a>';
                $content .= '</div>';
                echo $content;
                ?>
                
                </div>
                </div>
                <p>Did you notice a trend? Nickelback are undeniably one of the 00s biggest bands - and they’ve got numbers to back it up. Catch them on their Australian tour next year - <a href="https://www.livenation.com.au/artist/nickelback-tickets" target="_blank">grab your tickets here!</a></p>
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