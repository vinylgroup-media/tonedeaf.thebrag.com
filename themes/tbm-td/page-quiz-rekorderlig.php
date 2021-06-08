<?php /* Template Name: Quiz (Rekorderlig) */ ?>
<?php get_template_part( 'header-quiz-rekorderlig' );
$page_url = (get_permalink());
$page_title = get_the_title();
wp_enqueue_script( 'quiz-rekorderlig', get_template_directory_uri() . '/js/quiz-rekorderlig.js', array ( 'jquery' ), '20181030-1', true);
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
    $results = array(
        array(
            'title' => 'Ouch, our ears hurt',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result0.jpg?v=3',
        ),
        array(
            'title' => 'Woah, you’re out of tune',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result10.jpg?v=3',
        ),
        array(
            'title' => 'Woah, you’re out of tune',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result20.jpg?v=3',
        ),
        array(
            'title' => 'Keep practicing, it’s a long way to the top...',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result30.jpg?v=3',
        ),
        array(
            'title' => 'Keep practicing, it’s a long way to the top...',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result40.jpg?v=3',
            
        ),
        array(
            'title' => 'You need to head back to rock school',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result50.jpg?v=3',
        ),
        array(
            'title' => 'You need to head back to rock school',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result60.jpg?v=3',
        ),
        array(
            'title' => 'You\'re ready to play the mainstage',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result70.jpg?v=3',
        ),
        array(
            'title' => 'You\'re ready to play the mainstage',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result80.jpg?v=3',
        ),
        array(
            'title' => 'The hall of fame is calling your name',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result90.jpg?v=3',
        ),
        array(
            'title' => 'The hall of fame is calling your name',
            'text' => '',
            'img' => get_template_directory_uri() . '/images/quiz-rekorderlig/Rekorderling_result100.jpg?v=3',
        )
    );
    
    $questions = array(
        array(
            'question_no' => 1,
            'question' => 'Chance Waters',
            'answers' => array(
                'Working at an E-Sports company',
                'Studying a PHD',
                'Living in a hippie commune',
            ),
            'correct_answer' => 'Working at an E-Sports company'
        ),
        array(
            'question_no' => 2,
            'question' => 'Evermore (John Hume, lead singer)',
            'answers' => array(
                'Producer',
                'Pastor',
                'A&amp;R',
            ),
            'correct_answer' => 'Producer'
        ),
        
        array(
            'question_no' => 3,
            'question' => 'Operator Please (Drummer - need to find time)',
            'answers' => array(
                'Drums for PNAU',
                'Soccer player',
                'Pastry chef',
            ),
            'correct_answer' => 'Drums for PNAU'
        ),
        array(
            'question_no' => 4,
            'question' => 'Snakadaktal (Sophie & Joey)',
            'answers' => array(
                'Professional Iceskaters',
                'New project called Two People',
                'Nothing',
            ),
            'correct_answer' => 'New project called Two People'
        ),
        array(
            'question_no' => 5,
            'question' => 'Babaganouj (Hatchie)',
            'answers' => array(
                'Solo artist',
                'Went to art school',
                'Songwriter',
            ),
            'correct_answer' => 'Solo artist'
        ),
        array(
            'question_no' => 6,
            'question' => 'Funkoars (Trials)',
            'answers' => array(
                'Chilling out in Adelaide with the Hilltop Hoods, no music',
                '1 half of A.B Original and also produces movies',
                'Radio host in Adelaide',
            ),
            'correct_answer' => '1 half of A.B Original and also produces movies'
        ),
        array(
            'question_no' => 7,
            'question' => 'Pegz',
            'answers' => array(
                'Fled the country',
                'Is involved in a number of startups including a cannabis enterprise',
                'Rapping under the alias Lil Xan',
            ),
            'correct_answer' => 'Is involved in a number of startups including a cannabis enterprise'
        ),
        array(
            'question_no' => 8,
            'question' => 'Tigertown (Kurt Bailey, Charlie Collins)',
            'answers' => array(
                'Kurt manages Gang Of Youths and Charlie is a solo artist',
                'They both perform in a Silverchair cover band',
                'Kurt is homeless and Charlie teaches Science',
            ),
            'correct_answer' => 'Kurt manages Gang Of Youths and Charlie is a solo artist'
        ),
        array(
            'question_no' => 9,
            'question' => 'Little Red',
            'answers' => array(
                'They changed their name to Little Blue',
                'They\'re all firefighters now',
                'Members are part of the new bands New Gods, Naked Bodies, Major Tom and teh Atoms and The Hondas',
            ),
            'correct_answer' => 'Members are part of the new bands New Gods, Naked Bodies, Major Tom and teh Atoms and The Hondas'
        ),
        array(
            'question_no' => 10,
            'question' => 'The Bride',
            'answers' => array(
                'Members went onto form Hellions',
                'They got hitched',
                'They were never a band',
            ),
            'correct_answer' => 'Members went onto form Hellions'
        ),
        /*
        array(
            'question_no' => 11,
            'question' => 'Flowertruck',
            'answers' => array(
                'Vocalist Sarah Sykes is now in the band Sunscreen',
                'Running a roadside flower stop',
                'Now play under the name',
            ),
            'correct_answer' => 'Vocalist Sarah Sykes is now in the band Sunscreen'
        ),
         * 
         */
        array(
            'question_no' => 11,
            'question' => 'B Wise',
            'answers' => array(
                'Is now signed to Elefant Traks and released his debut album this year',
                'Joined the rap collective Brockhampton',
                'Produces beats for Hilltop Hoods',
            ),
            'correct_answer' => 'Is now signed to Elefant Traks and released his debut album this year'
        ),
        array(
            'question_no' => 12,
            'question' => 'Nick Nuisance &amp; The Delinquents',
            'answers' => array(
                'Gigging around Sydney and touring with bands like Polish Club and Straight Arrows',
                'Were arrested for public nuisance',
                'Have given up music to form a gang',
            ),
            'correct_answer' => 'Gigging around Sydney and touring with bands like Polish Club and Straight Arrows'
        ),
        array(
            'question_no' => 13,
            'question' => 'CLYPSO',
            'answers' => array(
                'Performs in Cirque De Soleil',
                'Opening for huge acts like PNAU and performing at festivals like Bigsound and Festival of the Sun',
                'Plays bass in a pop punk band',
            ),
            'correct_answer' => 'Opening for huge acts like PNAU and performing at festivals like Bigsound and Festival of the Sun'
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
        <div class="quiz-header" style="cursor: pointer;"><img src="<?php echo get_template_directory_uri(); ?>/images/quiz-rekorderlig/Rekorderling_quizTOP3_600px.jpg"></div>
        <div class="questions-wrap" id="questions-wrap">
            <div>
                <p>Rekorderlig Sauna Sounds are all about embracing the unique, quirky and innovative – together, we’re presenting some of country’s most trailblazing up and comers, getting them sweaty and ready to show off what makes them so special.</p>
                <p>Australia is home to some of the world’s most talented up and coming musicians - from hip hop luminary B Wise, summery electronic shaker CLYPSO and raucous punks Nick Nuisance & The Delinquents, Rekorderlig Cider have been on the search for the most forward thinking and unique acts our indie scene has to offer.</p>
                <p>With more artists to come, catch them all before they blow up.</p>
                <h3 style="text-transform: none; font-size: 130%">
                    Consider yourself an Aussie music expert?
                </h3>
                <p>Test your knowledge with this throwback to the Aus indie music of yesteryear.</p>
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
                        <input type="radio" class="radio answer_choice<?php echo $i == 0 ? ' answer_choice-rekorderlig-start' : ''; echo ( $i + 1 ) == count( $questions ) ? ' answer_choice-rekorderlig-last': ''; ?>" name="answers[<?php echo $question['question_no']; ?>]" id="answer_<?php echo $i . '_' . $j; ?>" value="<?php echo $answer_text; ?>">
                        <label for="answer_<?php echo $i . '_' . $j; ?>" class="answer_choice_label answer_choice_label-rekorderlig"><span><?php echo $answer_text; ?></span></label>
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
                
                <div style="position: absolute; bottom: 6%; right: 6.7%; width: 22%; " class="share-icons">
                    
                <?php
                $page_url = urlencode(get_permalink());
                $page_title = str_replace( ' ', '%20', get_the_title());
                
                $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u=' . $page_url;
                $twitterURL = 'https://twitter.com/intent/tweet?text=' . $page_title . '&amp;url=' . $page_url;

                $content = '<div class="social-share-buttons">';
                $content .= '<a class="social-share-link social-share-quiz-facebook" id="social-share-quiz-facebook" href="' . $facebookURL . '" target="_blank" data-type="share-fb" style="width: 50%;"><img src="' . get_template_directory_uri() . '/images/quiz-rekorderlig/FacebookCircle_rekorderlig.png?v=3"></a>';
                $content .= '<a class="social-share-link social-share-quiz-twitter" id="social-share-quiz-twitter" href="' . $twitterURL . '" target="_blank" data-type="share-twitter" style="width: 50%;"><img src="' . get_template_directory_uri() . '/images/quiz-rekorderlig/TwitterCircle_rekorderlig.png?v=3"></a>';
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