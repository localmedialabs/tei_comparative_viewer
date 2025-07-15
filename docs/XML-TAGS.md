## 🏷️ 使用可能な XML タグ一覧

```xml
<TEI xmlns="http://www.tei-c.org/ns/1.0"> <!-- 必須 -->
  <teiHeader> <!-- 必須 -->
    <fileDesc> <!-- 必須 -->
      <titleStmt> <!-- 必須 -->
        <title>タイトル</title> <!-- 必須 -->
        <author corresp="人名id">著者</author> <!-- 必須・繰り返し可能-->
        <author>差出人</author> <!-- 必須・繰り返し可能-->
        <author>作成者</author> <!-- 必須・繰り返し可能-->
        <editor>編者</editor> <!-- 繰り返し可能-->
        <editor>版元</editor> <!-- 繰り返し可能-->
        <respStmt>
          <resp>役割（翻刻、校閲、TEI符号化など）</resp> <!-- 繰り返し可能-->
          <name>担当者氏名</name> <!-- 繰り返し可能-->
        </respStmt>
      </titleStmt>
      <publicationStmt> <!-- 必須 -->
        <publisher>TEIテキストの出版社</publisher>
        <date>公開年月日</date>
        <availability>
          <licence target="URI">ライセンス名称</licence>
        </availability>
      </publicationStmt>
      <notesStmt>
        <note>注記</note> <!-- 繰り返し可能-->
      </notesStmt>
      <sourceDesc> <!-- 必須 -->
        <bibl>
          <title>タイトル</title>
          <author corresp="人名id">著者</author> <!-- 繰り返し可能-->
          <editor>編者/版元</editor> <!-- 繰り返し可能-->
          <date when="Year" from="Year" to="Year" notBefore="Year" notAfter="Year">出版年/製作年（年代）</date> <!-- 属性は「when」「from,to」「notBefore,notAfter」のいずれか -->
          <biblScope unit="volume">員数（典拠参照の範囲）</biblScope>
        </bibl>
        <msDesc>
          <msIdentifier>
            <country>国</country>
            <settlement>県名等</settlement>
            <institution>所蔵者</institution>
            <repository ref="URI">所蔵</repository>
            <collection>コレクション</collection> <!-- 繰り返し可能-->
            <idno type="URI">ハンドルURL</idno>
          </msIdentifier>
          <msContents>
            <summary>要約</summary>
            <msItem>
              <title type="cover">外題/表題</title>
              <title type="caption">内題</title>
              <title type="alt">首題・版心題/通用名称/各分野の専門的名称</title>
              <colophon>奥書</colophon>
            </msItem>
          </msContents>
          <physDesc>
            <objectDesc>
              <supportDesc>
                <support>料紙</support>
                <extent>丁数/紙数
                  <dimensions unit="cm">
                    <height>縦</height>
                    <width>横</width>
                    <depth>高さ</depth>
                  </dimensions>
                </extent>
              </supportDesc>
            </objectDesc>
            <decoDesc>
              <decoNote>付属品情報</decoNote>
            </decoDesc>
            <additions>
              <ab>識語</ab> <!-- 繰り返し可能-->
            </additions>
            <bindingDesc>
              <p>形態/品質・形状</p>
            </bindingDesc>
            <sealDesc>
              <p>蔵書印など</p> <!-- 繰り返し可能-->
            </sealDesc>
          </physDesc>
          <history>
            <origin>
              <origDate when="Year" from="Year" to="Year" notBefore="Year" notAfter="Year">完成年</origDate> <!-- 属性は「when」「from,to」「notBefore,notAfter」のいずれか -->
            </origin>
          </history>
          <provenance>
            <p>入手経緯のエピソード</p> <!-- 繰り返し可能-->
          </provenance>
          <acquisition>
            <name>旧蔵者/伝来過程</name> <!-- 繰り返し可能-->
          </acquisition>
        </msDesc>
      </sourceDesc>
    </fileDesc>
    <profileDesc>
      <correspDesc>
        <correspAction type="sent">
          <persName>差出人/作成者</persName> <!-- 繰り返し可能-->
          <date when="Year">送信年月日</date>
        </correspAction>
        <correspAction type="received">
          <persName>名宛人/受取人</persName> <!-- 繰り返し可能-->
          <date when="Year">受信年月日</date>
        </correspAction>
      </correspDesc>
      <textClass>
        <keywords>
          <term>主題</term> <!-- 繰り返し可能-->
        </keywords>
      </textClass>
    </profileDesc>
    <encodingDesc>
      <projectDesc>
        <p>プロジェクトの説明</p>
      </projectDesc>
      <editorialDecl>
        <p>凡例、規則</p>
      </editorialDecl>
    </encodingDesc>
  </teiHeader>
  <facsimile sameAs="IIIFManifestURI"> <!-- 必須-->
    <surface xml:id="画像id" ulx="0" uly="0" lrx="右下x座標" lry="右下y座標" sameAs="キャンバスid"> <!-- 必須・繰り返し可能-->
      <label>番号</label>
      <graphic width="幅" height="高さ" url="画像のURL" sameAs="画像の基底" />
      <zone xml:id="ゾーンid" ulx="左上x座標" uly="左上y座標" lrx="右下x座標" lry="右下y座標" /> <!-- 必須・繰り返し可能-->
    </surface>
  </facsimile>
  <text>
    <body style="writing-mode: vertical-rl;" xml:lang="ja"> <!-- 横書きは「 style="writing-mode: horizontal-tb;" xml:lang="en"」 -->
      <div type="翻刻/読み下し/現代語訳/解説"> <!-- 必須・繰り返し可能・type属性は4つのうち該当の値を指定-->
        <ab xml:id="文章id" corresp="文章id"> <!-- 一定のまとまりを区切る≒段落、属性は「翻刻」でxml:idを記述し、その他の場合correspを記述することで文章を紐づける -->
          <!-- 以下順不同、複数可、テキストに合わせて記述 -->
          <s xml:id="文章id" corresp="文章id">文章</s> <!-- abより更に細かい文のまとまり、属性は「翻刻」でxml:idを記述し、その他の場合correspを記述することで文章を紐づける -->
          <pb facs="ゾーンid" /> <!-- 半丁ごとに区切る -->
          <lb /> <!-- 行頭に与える -->
          <persName corresp="人名id">人名</persName> <!-- 人名 -->
          <placeName corresp="地名id">地名</placeName> <!-- 地名 -->
          <date when="Year" from="Year" to="Year" notBefore="Year" notAfter="Year">年月日</date> <!-- 属性は「when」「from,to」「notBefore,notAfter」のいずれか -->
          <metamark function="kaeri">㆑</metamark> <!-- 訓点 -->
          <ruby><rb>仮名</rb><rt>かな</rt></ruby> <!-- ふりがな -->
          <note type="割書"> <!-- 割書、割注 -->
            割書1
            <milestone unit="wbr"/> <!-- 割書内の改行 -->
            割書2
          </note>
          <note type="注"> <!-- 割書以外の注記 -->
            注記～
          </note>
          本文の該当箇所～<anchor type="noteEnd"/> <!-- <anchor type="noteEnd"/>が出現するまでが注記の対象となる該当箇所 -->
          <add>追記</add> <!-- 追加 -->
          <del>削除</del> <!-- 削除 -->
          <subst> <!-- 書き直し -->
            <del>削除</del> <!-- 削除部分 -->
            <add>追記</add> <!-- 追加部分 -->
          </subst>
          <figure> <!-- 画像 -->
            <head facs="ゾーンid">図の説明</head>
          </figure>
        </ab>
      </div>
    </body>
    <back> <!-- 人名、地名の一覧 -->
      <listPerson> <!-- 人名一覧 -->
        <person xml:id="人名id" n="idのヨミ"> <!-- 繰り返し可能 -->
          <persName>人名</persName> <!-- 繰り返し可能 -->
          <note>解説</note>
          <idno type="URI">人命に関する情報のURL</idno>
        </person>
      </listPerson>
      <listPlace> <!-- 地名一覧 -->
        <place xml:id="地名id"> <!-- 繰り返し可能 -->
          <placeName>地名</placeName> <!-- 繰り返し可能 -->
          <note>解説</note>
          <location>
            <geo>35.6813039 139.7644855</geo><!-- 座標、半角スペース区切りで「緯度 経度」のように記述 -->
          </location>
        </place>
      </listPlace>
    </back>
  </text>
</TEI>
```

### 補足

- corresp 属性や facs 属性に記述する xml:id には先頭に「#」をつける必要があります。
- 本プログラムに使用する TEI/XML ファイルを作成する際は、必須タグのみ記述した[テンプレート](./../template.xml)に必要なタグを追記し作成してください。
- 実際の使用例は[前近代日本 - アジア関係資料デジタルアーカイブ](https://asia-da.lit.kyushu-u.ac.jp/search)から資料を選択し、右上「ダウンロード」ボタンより xml ファイルを取得し確認できます。
