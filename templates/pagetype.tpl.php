<?php
    // In a real module variables should be manipulated in a preprocess function.
    $content = $element->content;
    $page    = $content['#element'];
?>

<section class="<?php print $classes; ?>">
    <h1 class="page__title"><?php print $page->title; ?></h1>

    <?php print render($content['page_body']); ?>
</div>
