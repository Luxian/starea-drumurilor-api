<?php

class ParserStareaDrumurilor {

    public static function parseSectionTitle($title) {
        $section = array(
            'number' => 0,
            'title' => '',
            'status' => '',
        );

        if (preg_match("/^\s?(\d+)\.\s?([\w\s]+)(\:[\w\s]+)?/", $title, $matches) === 1) {
            $section['number'] = isset($matches[1]) ? $matches[1] : 0;
            $section['title'] = isset($matches[2]) ? trim($matches[2]) : '';
            $section['status'] = isset($matches[3]) ? trim($matches[3]) : '';

        }

        return $section;
    }

    public static function extractTableData(DOMElement $table) {
        if ($table->nodeName != 'table') {
            throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '() expects "table" elements, "' . $table->nodeName . '" passed');
        }

        $table_headers = array();
        $table_rows = array();
        foreach($table->childNodes as $row_index => $tableRow) {
            if ($tableRow->nodeName != 'tr') {
                continue;
            }
            foreach($tableRow->childNodes as $column_index => $cell) {
                if ($cell->nodeName == 'th') {
                    $table_headers[$column_index] = trim($cell->textContent);
                }
                else {
                    $column_key = isset($table_headers[$column_index]) ? $table_headers[$column_index] : $column_index;
                    $table_rows[$row_index][$column_key] = $cell->textContent;
                }
            }
        }

        return $table_rows;
    }
}
