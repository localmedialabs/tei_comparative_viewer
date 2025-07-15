<script setup>
import { watch, onMounted, ref } from "vue";

const props = defineProps({
  output: String, // 表示HTML文字列
  hoverInfo: Object, // マウスホバーされたテキストに関する情報
});

// 親コンポーネントにテキストからの情報送信
const emit = defineEmits(["updateDisplayList", "clickText", "hoverAbTagText", "hoverSTagText", "leaveAbTagText", "leaveSTagText"]);

const componentName = "modernTransliteration";

const root = ref(null);

// 人名・地名リストの選択表示箇所送信
const updateDisplayList = (event) => {
  const corresp = event.currentTarget.getAttribute("corresp");
  corresp && emit("updateDisplayList", corresp);
};

// クリックテキストの情報送信
const clickText = (event) => {
  const facs = event.currentTarget.getAttribute("facs");
  facs && emit("clickText", facs);
};

// abタグのホバーテキストの情報送信
const hoverAbTagText = (event) => {
  const corresp = event.currentTarget.getAttribute("corresp");
  corresp && emit("hoverAbTagText", corresp, componentName);
};

// abタグのホバー解除の情報を送信
const leaveAbTagText = () => {
  emit("leaveAbTagText");
};

// sタグのホバーテキストの情報送信
const hoverSTagText = (event) => {
  const corresp = event.currentTarget.getAttribute("corresp");
  corresp && emit("hoverSTagText", corresp, componentName);
};

// sタグのホバー解除の情報を送信
const leaveSTagText = () => {
  emit("leaveSTagText");
};

// ホバーidと同一のcorrespを持つテキストにtei-hoverクラスを付与もしくは削除する関数
const hoverTextUpdateClass = (hoverInfo) => {
  if (hoverInfo.abId !== null) {
    const element = root.value.querySelector("[corresp='" + hoverInfo.abId + "'].tei-link_ab");
    element.classList.add("tei-hover_ab");
    if (hoverInfo.componentName !== componentName) {
      element.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  } else {
    const elements = document.querySelectorAll(".tei-hover_ab");
    elements.forEach((element) => {
      element.classList.remove("tei-hover_ab");
    });
  }
  if (hoverInfo.sId !== null) {
    const element = root.value.querySelector("[corresp='" + hoverInfo.sId + "'].tei-link_s");
    element.classList.add("tei-hover_s");
    if (hoverInfo.componentName !== componentName) {
      element.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  } else {
    const elements = document.querySelectorAll(".tei-hover_s");
    elements.forEach((element) => {
      element.classList.remove("tei-hover_s");
    });
  }
};

// ホバーテキスト情報の更新を監視
watch(
  () => props.hoverInfo,
  (hoverInfo) => {
    hoverTextUpdateClass(hoverInfo);
  },
  { deep: true }
);

// DOM描画後にイベント追加
onMounted(() => {
  // コンポーネントのルート要素を取得
  const rootElement = document.getElementById("modernTranslation");
  if (rootElement) {
    // correspを持つ人名、地名の要素を選択
    const names = rootElement.querySelectorAll("[corresp].tei-persName, [corresp].tei-placeName");
    names.forEach((name) => {
      // それぞれの要素にクリックイベントを追加
      name.addEventListener("click", updateDisplayList);
    });

    // correspを持つtei-link_abの要素を選択
    const abLinks = rootElement.querySelectorAll("[corresp].tei-link_ab");
    abLinks.forEach((link) => {
      // それぞれの要素にホバーイベントを追加
      link.addEventListener("mouseenter", hoverAbTagText);
      link.addEventListener("mouseleave", leaveAbTagText);
    });

    // correspを持つtei-link_sの要素を選択
    const sLinks = rootElement.querySelectorAll("[corresp].tei-link_s");
    sLinks.forEach((link) => {
      // それぞれの要素にホバーイベントを追加
      link.addEventListener("mouseenter", hoverSTagText);
      link.addEventListener("mouseleave", leaveSTagText);
    });

    // facsを持つiconの要素を選択
    const icons = rootElement.querySelectorAll("i[facs]");
    icons.forEach((icon) => {
      // それぞれの要素にクリックイベントを追加
      icon.addEventListener("click", clickText);
    });

    const spanElements = rootElement.querySelectorAll("span");
    spanElements.forEach((spanElement) => {
      spanElement.childNodes.forEach((node) => {
        if (node.nodeType === Node.TEXT_NODE) {
          // テキストノードに対してのみ処理
          const modifiedText = node.nodeValue.replace(/\d+/g, (match) => {
            return `<span class="tei-num">${match}</span>`;
          });

          // 新しいHTMLを挿入（HTMLとして解釈させる）
          const tempContainer = document.createElement("span");
          tempContainer.innerHTML = modifiedText;

          // 既存のテキストノードを置き換える
          node.replaceWith(...tempContainer.childNodes);
        }
      });
    });
  }
});
</script>
<template>
  <div class="p-3" v-html="output" ref="root"></div>
</template>
