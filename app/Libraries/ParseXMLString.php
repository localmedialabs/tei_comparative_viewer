<?php

namespace App\Libraries;



class ParseXMLString
{
  protected $doc;
  protected $writeMode;
  protected $names;
  protected $removes;
  protected static $handlers = null;

  /**
   * XMLノードをHTML要素に変換するためのコンストラクタ
   *
   * @param object $doc HTML要素を生成するためのドキュメントオブジェクト
   * @param string $writeMode 縦書きか横書きか
   * @param array $names テキスト内の人名、地名情報を収集する配列
   * @param array $removes 削除するノードの配列
   */
  public function __construct($doc, $writeMode = 'vertical', $names = ['persNames' => [], 'placeNames' => []], $removes = [])
  {
    $this->doc = $doc;
    $this->writeMode = $writeMode;
    $this->names = $names;
    $this->removes = $removes;

    if (is_null(self::$handlers)) {
      self::$handlers = $this->initializeHandlers();
    }
  }

  /**
   * XMLノードをHTML要素に変換する
   *
   * @param object $node 処理するXMLノード
   * @return object 変換されたHTML要素
   */
  public function convert($node)
  {
    $root = $this->processNode($node);
    return $root;
  }

  protected function initializeHandlers(): array
  {
    return [
      'p' => function ($node) {
        $el = $this->doc->createElement('p');
        $el->classes()->add('mx-2 mb-0');
        $this->processChildNodes($node, $el);
        return $el;
      },
      'ab' => function ($node) {
        $el = $node->hasAttribute('corresp')
          ? $this->processCorrespNode($node, 'tei-link_ab')
          : $this->processIdNode($node, 'tei-link_ab');
        $this->processChildNodes($node, $el);
        return $el;
      },
      's' => function ($node) {
        $el = $node->hasAttribute('corresp')
          ? $this->processCorrespNode($node, 'tei-link_s')
          : $this->processIdNode($node, 'tei-link_s');
        $this->processChildNodes($node, $el);
        return $el;
      },
      'pb' => function ($node) {
        $class = "tei-icon-{$this->writeMode} fa-regular fa-image text-black";
        if ($node->hasAttribute('facs')) {
          $el = $this->doc->createElement('i');
          $el->attr('facs', str_replace('#', '', $node->attr('facs')));
          $el->classes()->add($class);
        } else {
          $el = $this->createEmptyNode();
        }
        return $el;
      },
      'lb' => function () {
        $el = $this->doc->createElement('br');
        return $el;
      },
      'persname' => function ($node) {
        $el = $this->processCorrespNode($node, 'tei-persName');
        $id = $this->processNameNode($node, 'persNames');
        $el->attr('id', $id);
        $this->processChildNodes($node, $el);
        return $el;
      },
      'placename' => function ($node) {
        $el = $this->processCorrespNode($node, 'tei-placeName');
        $id = $this->processNameNode($node, 'placeNames');
        $el->attr('id', $id);
        $this->processChildNodes($node, $el);
        return $el;
      },
      'date' => function ($node) {
        $el = $this->doc->createElement('span');
        $el->classes()->add('tei-date');
        $this->processChildNodes($node, $el);
        return $el;
      },
      'metamark' => function ($node) {
        if ($node->hasAttribute('function') && $node->attr('function') === 'kaeri') {
          $el = $this->doc->createElement('span');
          $el->classes()->add('tei-return_point');
        } else {
          $el = $this->createEmptyNode();
        }
        $this->processChildNodes($node, $el);
        return $el;
      },
      'note' => function ($node) {
        if ($node->attr('type') === '割書') {
          // 割書の場合
          $el = $this->processSplitTextNode($node);
        } elseif ($node->attr('type') === '注') {
          // 注記の場合
          $el = $this->processNoteTextNode($node);
        } else {
          $el = $this->createEmptyNode();
        }
        return $el;
      },
      'subst' => function ($node) {
        $el = $this->doc->createElement('span');
        $el->classes()->add("tei-subst-{$this->writeMode}");
        $this->processChildNodes($node, $el);
        return $el;
      },
      'add' => function ($node) {
        if (in_array($this->writeMode, ['vertical', 'horizontal'])) {
          // 縦書きと横書きの場合分け
          $class = $this->writeMode === 'vertical' ? 'tei-subst-vertical' : 'tei-subst-horizontal';
          $styleKey = $this->writeMode === 'vertical' ? 'height' : 'width';
          // 高さや幅の値
          $styleValue = '0rem';

          $el = $this->doc->createElement('span');
          $el->classes()->add('tei-add');
          $this->processChildNodes($node, $el);

          if ($node->parent()->tagName() === "subst") {
            return $el;
          } else {
            $parentEl = $this->doc->createElement('span');
            $parentEl->classes()->add($class);
            $parentEl->style()->setProperty($styleKey, $styleValue);
            $parentEl->appendChild($el);
            return $parentEl;
          }
        }
      },
      'del' => function ($node) {
        $el = $this->doc->createElement('span');
        $el->classes()->add('tei-del_text');
        $this->processChildNodes($node, $el);
        $parentEl = $this->doc->createElement('span');
        $parentEl->classes()->add('tei-del_line');
        $parentEl->appendChild($el);
        return $parentEl;
      },
      'figure' => function ($node) {
        $el = $this->doc->createElement('span');
        $el->classes()->add('tei-figure text-primary');
        foreach ($node->children() as $child) {
          if (!$child->isTextNode()) {
            // 'head' タグで 'facs' 属性があれば処理
            if ($child->tagName() === 'head' && $child->hasAttribute('facs')) {
              $el->attr('facs', str_replace('#', '', $child->attr('facs')));
            }
          }
        }
        $this->processChildNodes($node, $el);
        return $el;
      }
    ];
  }

  /**
   * XMLノードをHTML要素に変換するためのメイン処理
   *
   * @param object $node 処理するXMLノード
   * @return object 変換されたHTML要素
   */
  protected function processNode($node)
  {
    if ($node->isTextNode()) {
      $el = $this->doc->createTextNode($node->text());
      return $el;
    }

    // ノードのタグ名を小文字に変換
    // 変換されたタグ名がhandlers配列に存在する場合は、対応する処理を実行
    // 存在しない場合は、デフォルトの処理を実行
    $tagName = \Str::lower($node->tagName());
    if (isset(self::$handlers[$tagName])) {
      return self::$handlers[$tagName]($node);
    } else {
      $el = $this->doc->createElement($tagName);
      $this->processChildNodes($node, $el);
      return $el;
    }
  }

  /**
   * 子ノードを処理して、親ノードに追加する
   *
   * @param object $fromNode 処理するXMLノード
   * @param object $toParent 変換されたHTML要素の親ノード
   */
  protected function processChildNodes($fromNode, $toParent)
  {
    foreach ($fromNode->children() as $child) {
      // $this->removesが空ではない時
      if (!empty($this->removes)) {
        if (in_array($child->html(), $this->removes)) {
          // removesのキーを取得
          $key = array_search($child->html(), $this->removes);
          // removesのキーを削除
          unset($this->removes[$key]);
          // 削除するノードはスキップ
          continue;
        }
      }

      $converted = $this->processNode($child);
      if ($converted !== null) {
        $toParent->appendChild($converted);
      }
    }
  }

  /**
   * corresp属性のあるノードを処理して、HTML要素を生成する
   *
   * @param object $node 処理するXMLノード
   * @param string $class 生成するHTML要素のクラス名
   * @return object 変換されたHTML要素
   */
  protected function processCorrespNode($node, $class)
  {
    // corresp属性から"#"を除外
    $corresp = str_replace('#', '', $node->attr('corresp'));
    $span = $this->doc->createElement('span');
    $span->classes()->add($class);
    $span->attr('corresp', $corresp);
    return $span;
  }

  /**
   * 人名や地名のノードを処理して、HTML要素を生成する
   *
   * @param object $node 処理するXMLノード
   * @param string $name 人名または地名の種類
   * @return string 生成されたID
   */
  protected function processNameNode($node, $name)
  {
    $corresp = str_replace('#', '', $node->attr('corresp'));
    if (!array_key_exists($corresp, $this->names[$name])) {
      $this->names[$name][$corresp] = [];
    }
    $nameHtml = $node->innerHtml();
    array_push($this->names[$name][$corresp], $nameHtml);
    return $corresp . '_' .  count($this->names[$name][$corresp]);
  }

  /**
   * ID属性のあるノードを処理して、HTML要素を生成する
   *
   * @param object $node 処理するXMLノード
   * @param string $class 生成するHTML要素のクラス名
   * @return object 変換されたHTML要素
   */
  protected function processIdNode($node, $class)
  {
    $span = $this->doc->createElement('span');
    $span->classes()->add($class);
    $attributes = $node->attributes();
    $id = $attributes['id'] ?? null;
    if ($id) {
      $span->attr('id', $id);
    }
    return $span;
  }

  /**
   * 割書ノードを処理して、HTML要素を生成する
   *
   * @param object $node 処理するXMLノード
   * @return object 変換されたHTML要素
   */
  protected function processSplitTextNode($node)
  {
    $textRight = $this->doc->createElement('span');
    $textRight->classes()->add('tei-split_text-right');
    $textLeft = $this->doc->createElement('span');
    $textLeft->classes()->add('tei-split_text-left');
    // milestoneの後の要素かどうか
    $isMilestone = false;

    foreach ($node->children() as $child) {
      if ($child->html() === "") {
        continue;
      } elseif (!$child->isTextNode() && $child->tagName() === 'milestone') {
        // milestoneノードの場合
        // 次のノードを取得
        $isMilestone = true;
      } else {
        $converted = $this->processNode($child);
        if ($converted !== null) {
          if ($isMilestone) {
            $textLeft->appendChild($converted);
          } else {
            $textRight->appendChild($converted);
          }
        }
      }
    }
    $span = $this->doc->createElement('span');
    $span->classes()->add('tei-split_text');
    $span->appendChild($textRight);
    $span->appendChild($textLeft);
    return $span;
  }

  /**
   * 注記ノードを処理して、HTML要素を生成する
   *
   * @param object $node 処理するXMLノード
   * @return object 変換されたHTML要素
   */
  protected function processNoteTextNode($node)
  {
    $nextNode = $node->nextSibling();
    $currentNode = $nextNode;
    $nodes = [];
    $existAnchor = false;
    // 念のため上限を設ける（ループの安全策）
    $maxIterations = 100;
    $iterations = 0;

    while ($currentNode !== null && $iterations++ < $maxIterations) {
      if (!$currentNode->isTextNode() && $currentNode->tagName() === 'anchor') {
        $existAnchor = true;
        break;
      }

      $nodes[] = $currentNode;
      $currentNode = $currentNode->nextSibling();
    }
    if ($existAnchor) {
      $target = $this->doc->createElement('span');
      $target->classes()->add("tei-note-{$this->writeMode}_target");
      foreach ($nodes as $n) {
        $converted = $this->processNode($n);
        if ($converted !== null) {
          $target->appendChild($converted);
        }
        $this->removes[] = $n;
      }
    }

    $el = $this->doc->createElement('span');
    $el->classes()->add("tei-note-{$this->writeMode}");
    if ($existAnchor) {
      $el->appendChild($target);
    }
    $span = $this->doc->createElement('span');
    $span->classes()->add("tei-note-{$this->writeMode}_text");
    $this->processChildNodes($node, $span);
    $el->appendChild($span);
    return $el;
  }

  /**
   * 空のノードを生成する
   *
   * @return object 変換されたHTML要素
   */
  protected function createEmptyNode()
  {
    $el = $this->doc->createElement('span');
    $el->classes()->add('none');
    return $el;
  }

  /**
   * namesプロパティを取得する
   *
   * @return array namesプロパティ
   */
  public function getNames()
  {
    return $this->names;
  }
}
