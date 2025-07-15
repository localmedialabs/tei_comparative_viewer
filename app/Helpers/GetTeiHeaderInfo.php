<?php
// TEIファイルのヘッダーから情報を取得するためのヘルパー関数

// ヘルパー関数の定義
if (! function_exists('getElementText')) {
  /**
   * SimpleXMLElementからテキストを文字列で取得する関数
   *
   * @param SimpleXMLElement|null $element 対象のXML要素
   * @param string $default 初期値
   * @return string テキスト
   */
  function getElementText($element, $default = "")
  {
    return $element && !isOnlyWhitespaceOrNewlines((string)$element) ? (string)$element : $default;
  }
}

if (! function_exists('getAttribute')) {
  /**
   * SimpleXMLElementから属性の値を文字列で取得する関数
   *
   * @param SimpleXMLElement|null $element 対象のXML要素
   * @param string $attribute 属性名
   * @param string $default 初期値
   * @return string 指定した属性の値
   */
  function getAttribute($element, $attribute, $default = "")
  {
    return $element ? (string)$element[$attribute] : $default;
  }
}

if (! function_exists('getArray')) {
  /**
   * SimpleXMLElementからテキストを配列で取得する関数
   *
   * @param SimpleXMLElement|null $element 対象のXML要素
   * @return array テキストを値とする配列
   */
  function getArray($element)
  {
    // 配列を取得し、空文字を除外
    return $element ? array_values(array_filter(array_map('strval', iterator_to_array($element, false)), function ($value) {
      return trim($value) !== '';  // 空文字や空白のみの値を除外
    })) : [];
  }
}

if (! function_exists('getAttributeTextMap')) {
  /**
   * SimpleXMLElementから属性をキー、テキストを値とする連想配列を取得する関数
   *
   * @param SimpleXMLElement|null $element 対象のXML要素
   * @param string $attribute 属性名
   * @return array 属性をキー、テキストを値とする連想配列
   */
  function getAttributeTextMap($element, $attribute)
  {
    if (!$element) {
      return [];
    }

    $result = [];
    foreach ($element as $child) {
      $key = isset($child[$attribute]) ? (string)$child[$attribute] : null;
      $value = (string)$child;

      if ($key !== null) {
        $result[$key] = $value;
      }
    }

    return $result;
  }
}
if (! function_exists('getRespNameMap')) {
  /**
   * respをキー、nameを値として取得する関数
   *
   * @param SimpleXMLElement|null $elements 対象のXML要素群
   * @return array respをキー、nameを値とする連想配列
   */
  function getRespNameMap($elements)
  {
    if (!$elements) {
      return [];
    }

    $result = [];
    foreach ($elements as $element) {
      $resp = isset($element->resp) ? (string)$element->resp : null;
      $name = isset($element->name) ? (string)$element->name : null;

      if ($resp !== null && $name !== null) {
        $result[$resp] = $name;
      }
    }

    return $result;
  }
}

if (! function_exists('getArrayFullText')) {
  /**
   * SimpleXMLElementからタグを除去して全ての文字列を配列で取得する関数
   *
   * @param SimpleXMLElement|null $element 対象のXML要素
   * @param string $removeTag 除外する要素名
   * @return array テキストを値とする配列
   */
  function getArrayFullText($elements, $removeTag)
  {
    $texts = [];
    foreach ($elements as $element) {
      // XML文字列を取得
      $text = $element->asXML();
      // 現在のタグ名と指定されたタグを正規表現で除去
      $text = preg_replace([
        '/<\/?' . preg_quote($element->getName(), '/') . '>/',
        '/<\/?' . preg_quote($removeTag, '/') . '>/',
      ], '', $text);

      // 連続するスペースと改行を除外
      $cleanedText = preg_replace('/\s+/', ' ', $text);

      array_push($texts, $cleanedText);
    }
    return $texts;
  }
}
if (! function_exists('isOnlyWhitespaceOrNewlines')) {
  /**
   * 正規表現で改行コードと半角スペース以外の文字があるかをチェック
   *
   * @param string $string 対象の文字列
   * @return boolean
   */
  function isOnlyWhitespaceOrNewlines($string)
  {
    return preg_match('/^\s*$/', $string) === 1;
  }
}
