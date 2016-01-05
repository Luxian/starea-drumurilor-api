<?php
// URL to parse
$url = "http://213.177.10.50:6060/itn/drumuri.asp";

/**
 * @var array
 *  Default array to return
 */
$reponse = array(
    'title' => '',
    'subtitle' => '',
    'sections' => array(),
);

// Be nice!
error_reporting(E_ALL);

require __DIR__ . '/ParserStareaDrumurilor.php';

// Get all content
$content = file_get_contents($url);

if ($content === FALSE) {
    // Content could not be fetched. abort!
    exit(1);
}

$dom = new DOMDocument();
$dom->loadHTML($content);


$body_tags = $dom->getElementsByTagName('body');
if ($body_tags->length !== 1) {
    exit(2); // something went wrong!
}

// Get body text and normalize it
$body = $body_tags->item(0)->cloneNode(TRUE);
$body->normalize();

// Get the first div from the body
$first_div = false;
foreach($body->childNodes as $element) {
    if ($element->nodeName == 'div') {
        $first_div = $element;
        break;
    }
}
if (!$first_div) {
    exit(3); // unknown HTML structure
}

// Start parsing the content
$ignored_elements = array('basefont', 'br', '#text');
$current_section_key = '';
foreach($first_div->childNodes as $html_element) {
    // skip ignored elements
    if (in_array($html_element->nodeName, $ignored_elements)) {
        continue;
    }

    switch($html_element->nodeName) {
        case 'h1':
            // page title
            $reponse['title'] = $html_element->textContent;
            break;

        case 'h6':
            $reponse['subtitle'] = $html_element->textContent;
            break;

        case 'h4':
            $section_title = trim($html_element->textContent);
            $section = ParserStareaDrumurilor::parseSectionTitle($section_title);
            $current_section_key = 'section_' . $section['number'];
            $reponse['sections'][$current_section_key]['info'] = $section;
            $reponse['sections'][$current_section_key]['data'] = array();
            break;

        case 'ul':
            if ($current_section_key != '') {
                $reponse['sections'][$current_section_key]['data'][] = $html_element->textContent;
            }
            break;

        case 'table':
            if ($current_section_key != '') {
                $reponse['sections'][$current_section_key]['data'][] = ParserStareaDrumurilor::extractTableData($html_element);
            }
            break;

        default:
            //var_dump($html_element->nodeName);
    }
}

header('Content-Type: text/javascript; charset=utf8');
echo json_encode($reponse);
exit(0);
