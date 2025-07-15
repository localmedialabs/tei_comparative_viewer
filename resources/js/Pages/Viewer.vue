<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import { Splitpanes, Pane } from "splitpanes";
import "splitpanes/dist/splitpanes.css";

import MainLayout from "@/Layouts/MainLayout.vue"; // Layout
import TeiHeader from "@/Components/Header.vue"; // TEIビューワー内のヘッダー
import Transcription from "@/Components/Transcription.vue"; // 翻刻コンポーネント
import Transliteration from "@/Components/Transliteration.vue"; // 読み下し文コンポーネント
import ModernTranslation from "@/Components/ModernTranslation.vue"; // 現代語訳コンポーネント
import Osd from "@/Components/Osd.vue"; // IIIFビューワーコンポーネント（OpenSeadragon）
import Audio from "@/Components/Audio.vue"; // オーディオ再生表示コンポーネント
import About from "@/Components/About.vue"; // 概要表示コンポーネント
import Bibliography from "@/Components/Bibliography.vue"; // 書誌情報表示コンポーネント
import Place from "@/Components/Place.vue"; // 地図コンポーネント（Leaflet）
import List from "@/Components/List.vue"; // 人名・地名リスト表示コンポーネント

const props = defineProps({
  manifest_data: Object, // IIIFマニフェストデータ
  tei_url: String, // TEIファイルのURL
  bibliography: Object, // 書誌情報オブジェクト
  zones: Object, // 画像の座標情報オブジェクト
  is_one_page: Boolean, // 対象のIIIF画像が単一ページか判別
  text_info: Object, // 言語、記述方向などテキスト情報オブジェクト
  outputs: Object, // 表示するHTML文字列を表示コンポーネント別に格納したオブジェクト
  people: Object, // 人名一覧オブジェクト
  places: Object, // 地名一覧オブジェクト
  coordinate: Array, // 地図に表示する座標の初期値
  audio_url: String, // オーディオファイルURL
});

const listId = ref(null); // リスト上でホバーされたID
const coordinates = ref(props.coordinate); // 地図に表示する座標
const displayList = ref(null); // 人名・地名リストの選択表示箇所
const selectPage = ref(null); // IIIFの指定ページ
const selectZone = ref(null); // IIIFの指定座標
const eventListenersMap = new Map(); // リスナー登録状況を追跡するマップ
const hoverInfo = ref({
  abId: null,
  sId: null,
  componentName: null,
});
const selectComponents = ref({
  Transcription: props.outputs.transcription ? true : false, // 翻刻
  Transliteration: props.outputs.transliteration ? true : false, // 読み下し
  ModernTranslation: props.outputs.modernTranslation ? true : false, // 現代語訳
  Osd: true, // OpenSeadragon
  Audio: props.audio_url ? true : false, // オーディオ
  About: props.bibliography.summary ? true : false, // 概要
  Bibliography: true, // 書誌事項
  Place: true, // 地図情報
  List: true, // 人名・地名リスト
});
// 分割表示のサイズ
const sizes = ref({
  left: 40,
  center: 35,
  right: 25,
  transcription: 34,
  transliteration: 33,
  modernTranslation: 33,
  osd: 60,
  audio: 20,
  about: 20,
  bibliography: 20,
  place: 40,
  list: 40,
});

// 表示コンポーネントの変更を子コンポーネントから受け取り更新
const handleUpdateSelectComponent = (newSelectedComponent) => {
  selectComponents.value = { ...newSelectedComponent }; // 子から受け取った値で更新
};
// 表示コンポーネントが変更されたのち処理
watch(selectComponents, async () => {
  await nextTick(); // DOMが更新されるのを待つ
  // スクロールイベント更新
  handleScrollEvent();
});
// リスト表示のホバーIDの変更を子コンポーネントから受け取り更新
const handleUpdateListId = (newListId) => {
  listId.value = newListId;
};
// 地図表示座標の変更を子コンポーネントから受け取り更新
const handleUpdateCoordinates = (newCoordinates) => {
  if (JSON.stringify(coordinates.value) !== JSON.stringify(newCoordinates)) {
    coordinates.value = newCoordinates;
  }
};
// 人名・地名リストの選択表示箇所の変更を子コンポーネントから受け取り更新
const handleUpdateDisplayList = (newDisplayList) => {
  displayList.value = newDisplayList;
};
// テキストのクリックを子コンポーネントから受け取り、IIIFのページと座標を更新
const handleClickText = (facs) => {
  const page = props.zones[facs].label;
  selectPage.value = page;
  selectZone.value = {
    ulx: Number(props.zones[facs]["ulx"]),
    uly: Number(props.zones[facs]["uly"]),
    lrx: Number(props.zones[facs]["lrx"]),
    lry: Number(props.zones[facs]["lry"]),
  };
};
// abタグのホバーを子コンポーネントから受け取り、abタグのidとIIIFの座標を更新
const handleHoverAbTagText = (id, componentName) => {
  hoverInfo.value.abId = id;
  hoverInfo.value.componentName = componentName;
};
// sタグのホバーを子コンポーネントから受け取り、sタグのidとIIIFの座標を更新
const handleHoverSTagText = (id, componentName) => {
  hoverInfo.value.sId = id;
  hoverInfo.value.componentName = componentName;
};
// abタグのホバー解除を子コンポーネントから受け取りホバーに関わる変数のリセット
const handleLeaveAbTagText = () => {
  hoverInfo.value.abId = null;
};
// sタグのホバー解除を子コンポーネントから受け取りホバーに関わる変数のリセット
const handleLeaveSTagText = () => {
  hoverInfo.value.sId = null;
};
// リスナーの登録状況を確認
const hasWheelListener = (id) => {
  return eventListenersMap.has(id);
};
// TEIテキスト上のスクロール方向制御
const handleScrollEvent = () => {
  const components = [
    { id: "transcription", value: selectComponents.value.Transcription },
    { id: "transliteration", value: selectComponents.value.Transliteration },
    { id: "modernTranslation", value: selectComponents.value.ModernTranslation },
  ];

  components.forEach(({ id, value }) => {
    const container = document.getElementById(id);

    if (!value && hasWheelListener(id)) {
      // イベントリスナーを削除
      eventListenersMap.delete(id);
    } else if (value && container && !hasWheelListener(id)) {
      // イベントリスナーを追加
      container.addEventListener("wheel", (e) => {
        container.scrollLeft -= e.deltaY;
      });
      eventListenersMap.set(id, "wheel"); // 登録したリスナーを記録
    }
  });
};
// DOM描画後処理
onMounted(() => {
  // スクロールイベント更新
  handleScrollEvent();
});
</script>

<template>
  <MainLayout :noFooter="true">
    <div class="tei d-none d-lg-block">
      <TeiHeader :title="bibliography.title" :selectComponents="selectComponents" @update-selectComponents="handleUpdateSelectComponent" :tei_url="tei_url" />
      <div id="app">
        <Splitpanes>
          <Pane :size="sizes.left" v-if="selectComponents.Transcription || selectComponents.Transliteration || selectComponents.ModernTranslation">
            <Splitpanes horizontal>
              <Pane v-if="selectComponents.Transcription" :size="sizes.transcription">
                <div class="panel-container d-flex pe-2 pt-2" :class="text_info.writeMode" id="transcription">
                  <Transcription
                    :output="outputs.transcription"
                    :hoverInfo="hoverInfo"
                    :listId="listId"
                    @updateDisplayList="handleUpdateDisplayList"
                    @clickText="handleClickText"
                    @hoverAbTagText="handleHoverAbTagText"
                    @hoverSTagText="handleHoverSTagText"
                    @leaveAbTagText="handleLeaveAbTagText"
                    @leaveSTagText="handleLeaveSTagText"
                  ></Transcription>
                </div>
              </Pane>
              <Pane v-if="selectComponents.Transliteration" :size="sizes.transliteration">
                <div class="panel-container d-flex pe-2 pt-2" :class="text_info.writeMode" id="transliteration">
                  <Transliteration
                    :output="outputs.transliteration"
                    :hoverInfo="hoverInfo"
                    @updateDisplayList="handleUpdateDisplayList"
                    @clickText="handleClickText"
                    @hoverAbTagText="handleHoverAbTagText"
                    @hoverSTagText="handleHoverSTagText"
                    @leaveAbTagText="handleLeaveAbTagText"
                    @leaveSTagText="handleLeaveSTagText"
                  ></Transliteration>
                </div>
              </Pane>
              <Pane v-if="selectComponents.ModernTranslation" :size="sizes.modernTranslation">
                <div class="panel-container d-flex pe-2 pt-2" :class="text_info.writeMode" id="modernTranslation">
                  <ModernTranslation
                    :output="outputs.modernTranslation"
                    :hoverInfo="hoverInfo"
                    @updateDisplayList="handleUpdateDisplayList"
                    @clickText="handleClickText"
                    @hoverAbTagText="handleHoverAbTagText"
                    @hoverSTagText="handleHoverSTagText"
                    @leaveAbTagText="handleLeaveAbTagText"
                    @leaveSTagText="handleLeaveSTagText"
                  ></ModernTranslation>
                </div>
              </Pane>
            </Splitpanes>
          </Pane>
          <Pane v-if="selectComponents.Osd || selectComponents.About" :size="sizes.center">
            <Splitpanes horizontal>
              <Pane v-if="selectComponents.Osd" :size="sizes.osd">
                <Osd :manifest_data="manifest_data" :page="selectPage" :selectZone="selectZone" :hoverInfo="hoverInfo" :writeMode="text_info.writeMode" />
              </Pane>
              <Pane v-if="selectComponents.Audio" :size="sizes.audio">
                <div class="panel-container scroll">
                  <Audio :audio_url="audio_url" />
                </div>
              </Pane>
              <Pane v-if="selectComponents.About" :size="sizes.about">
                <div class="panel-container scroll">
                  <About :summary="bibliography.summary" />
                </div>
              </Pane>
            </Splitpanes>
          </Pane>
          <Pane v-if="selectComponents.Bibliography || selectComponents.Place || selectComponents.List" :size="sizes.right">
            <Splitpanes horizontal>
              <Pane v-if="selectComponents.Bibliography" :size="sizes.bibliography">
                <div class="panel-container scroll">
                  <Bibliography :bibliography="bibliography" />
                </div>
              </Pane>
              <Pane v-if="selectComponents.Place" :size="sizes.place">
                <Place :coordinates="coordinates" />
              </Pane>
              <Pane v-if="selectComponents.List" id="list" :size="sizes.list">
                <div class="panel-container scroll">
                  <List :people="people" :places="places" :displayList="displayList" @updateListId="handleUpdateListId" @updateCoordinates="handleUpdateCoordinates" />
                </div>
              </Pane>
            </Splitpanes>
          </Pane>
        </Splitpanes>
      </div>
    </div>
    <div class="d-lg-none">
      <p>このビューアーは画面幅が992px以上でないとご覧いただけません。</p>
    </div>
  </MainLayout>
</template>
<style lang="scss" scoped>
$tei-header-height: 60px;
.tei {
  .tei-header {
    height: $tei-header-height;
  }
  #app {
    height: calc(100vh - var(--header-height) - $tei-header-height); // ヘッダーの高さを計算しそれ以外をビューワーの高さに設定
    flex-direction: column;
  }
}
</style>
