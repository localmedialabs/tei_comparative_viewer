<?php
// TEIファイル登録時の処理をまとめたヘルパー関数

// ヘルパー関数の定義
if (! function_exists('getTagNames')) {
  /**
   * XML要素からタグ名を取得する関数
   *
   * @param SimpleXMLElement|null $element 対象のXML要素
   * @return array
   */
  function getTagNames($xml)
  {
    $tags = [];

    // 現在の要素のタグ名を取得
    $tags[] = $xml->getName();

    // 子要素がある場合、再帰的にタグ名を取得
    foreach ($xml->children() as $child) {
      $tags = array_merge($tags, getTagNames($child));
    }

    // 重複を排除して返す
    return array_unique($tags);
  }
}

if (!function_exists('getAllAttributeNames')) {
  /**
   * XML要素から全ての属性名を取得する関数
   *
   * @param SimpleXMLElement $element 対象のXML要素
   * @param array $attributeNames 属性名の配列
   * @return array
   */
  function getAllAttributeNames($element, &$attributeNames = [])
  {
    // 現在の要素の属性名を取得
    foreach ($element->attributes() as $name => $value) {
      if (!in_array($name, $attributeNames)) {
        $attributeNames[] = $name;
      }
    }

    // 子要素を再帰的に探索
    foreach ($element->children() as $child) {
      getAllAttributeNames($child, $attributeNames);
    }

    return $attributeNames;
  }
}

if (!function_exists('getAttributeValues')) {
  /**
   * XML要素から指定された属性の値を取得する関数
   *
   * @param SimpleXMLElement $element 対象のXML要素
   * @param string $attributeName 属性名
   * @param array $values 属性値の配列
   * @return array
   */
  function getAttributeValues($element, $attributeName, &$values = [])
  {
    // 現在の要素に指定された属性があれば値を取得
    foreach ($element->attributes() as $name => $value) {
      if ($name == $attributeName) {
        $values[] = (string) $value;
      }
    }

    // 子要素を再帰的に探索
    foreach ($element->children() as $child) {
      getAttributeValues($child, $attributeName, $values);
    }

    return $values;
  }
}

if (!function_exists('getSpecifiedAttributesWithTags')) {
  /**
   * XML要素から指定された属性とその属性を持つタグ名を取得する関数
   *
   * @param SimpleXMLElement $element 対象のXML要素
   * @param array $targetAttributes 取得したい属性名の配列
   * @param array $attributeTagMap 属性名とその属性を持つタグ名のマップ
   * @return void
   */
  function getSpecifiedAttributesWithTags($element, $targetAttributes, &$attributeTagMap = [])
  {
    // 現在の要素の属性を取得
    foreach ($element->attributes() as $attrName => $attrValue) {
      if (in_array($attrName, $targetAttributes)) {
        $tagName = $element->getName();
        if (!isset($attributeTagMap[$attrName])) {
          $attributeTagMap[$attrName] = [];
        }
        if (!in_array($tagName, $attributeTagMap[$attrName])) {
          $attributeTagMap[$attrName][] = $tagName;
        }
      }
    }

    // 子要素も再帰的に探索
    foreach ($element->children() as $child) {
      getSpecifiedAttributesWithTags($child, $targetAttributes, $attributeTagMap);
    }
  }
}
