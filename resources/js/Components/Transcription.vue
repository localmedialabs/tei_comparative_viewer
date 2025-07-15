<script setup>
import { watch, onMounted } from "vue";

const props = defineProps({
  output: String, // 表示HTML文字列
  hoverInfo: Object, // マウスホバーされたテキストに関する情報
  listId: String, // リスト上でホバーされたid
});

// 親コンポーネントにテキストからの情報送信
const emit = defineEmits(["updateDisplayList", "clickText", "hoverAbTagText", "hoverSTagText", "leaveAbTagText", "leaveSTagText"]);

const componentName = "transcription";

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
  const id = event.currentTarget.getAttribute("id");
  id && emit("hoverAbTagText", id, componentName);
};

// abタグのホバー解除の情報を送信
const leaveAbTagText = () => {
  emit("leaveAbTagText");
};

// sタグのホバーテキストの情報送信
const hoverSTagText = (event) => {
  const id = event.currentTarget.getAttribute("id");
  id && emit("hoverSTagText", id, componentName);
};

// sタグのホバー解除の情報を送信
const leaveSTagText = () => {
  emit("leaveSTagText");
};

// ホバーidと同一のidを持つテキストにtei-hover_abクラスやtei-hover_sクラスを付与もしくは削除する関数
const hoverTextUpdateClass = (hoverInfo) => {
  if (hoverInfo.abId !== null) {
    const element = document.querySelector("[id='" + hoverInfo.abId + "'].tei-link_ab");
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
    const element = document.querySelector("[id='" + hoverInfo.sId + "'].tei-link_s");
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

// リストでホバーされたlist_idと同様のidを持つ要素にtei-hover_list_idクラスを付与もしくは削除する関数
const hoverIdUpdateClass = (listId) => {
  // クリック用
  const elements = document.querySelectorAll(".tei-hover_list_id");
  elements.forEach((element) => {
    element.classList.remove("tei-hover_list_id");
  });
  const element = document.querySelector("[id='" + listId + "']");
  element.classList.add("tei-hover_list_id");
  element.scrollIntoView({ behavior: "smooth", block: "center" });
};

// ホバーテキスト情報の更新を監視
watch(
  () => props.hoverInfo,
  (hoverInfo) => {
    hoverTextUpdateClass(hoverInfo);
  },
  { deep: true }
);

// リストidの更新を監視
watch(
  () => props.listId,
  (listId) => {
    hoverIdUpdateClass(listId);
  },
  { deep: true }
);

// DOM描画後にイベント追加
onMounted(() => {
  // コンポーネントのルート要素を取得
  const rootElement = document.getElementById("transcription");
  if (rootElement) {
    // correspを持つ人名、地名の要素を選択
    const names = rootElement.querySelectorAll("[corresp].tei-persName, [corresp].tei-placeName");
    names.forEach((name) => {
      // それぞれの要素にクリックイベントを追加
      name.addEventListener("click", updateDisplayList);
    });

    // idを持つtei-link_abの要素を選択
    const abLinks = rootElement.querySelectorAll("[id].tei-link_ab");
    abLinks.forEach((link) => {
      // それぞれの要素にクリックイベントを追加
      link.addEventListener("mouseenter", hoverAbTagText);
      link.addEventListener("mouseleave", leaveAbTagText);
    });

    // idを持つtei-link_sの要素を選択
    const sLinks = rootElement.querySelectorAll("[id].tei-link_s");
    sLinks.forEach((link) => {
      // それぞれの要素にクリックイベントを追加
      link.addEventListener("mouseenter", hoverSTagText);
      link.addEventListener("mouseleave", leaveSTagText);
    });

    // facsを持つiconの要素を選択
    const icons = rootElement.querySelectorAll("i[facs]");
    icons.forEach((icon) => {
      // それぞれの要素にクリックイベントを追加
      icon.addEventListener("click", clickText);
    });

    // facsを持つfigureの要素を選択
    const figures = rootElement.querySelectorAll("[facs].tei-figure");
    figures.forEach((figure) => {
      // それぞれの要素にクリックイベントを追加
      figure.addEventListener("click", clickText);
    });

    // tei-subst-verticalクラスを持つ要素の高さ等の調整;
    const substVerticalElements = rootElement.querySelectorAll(".tei-subst-vertical");
    substVerticalElements.forEach((substVerticalElement) => {
      const delTextElement = substVerticalElement.querySelector(".tei-del_text");
      const addTextElement = substVerticalElement.querySelector(".tei-add");
      if (addTextElement) {
        const addTextHeight = addTextElement.offsetHeight;
        let delTextHeight = 0;
        if (delTextElement) {
          // tei-del_text の幅を取得
          delTextHeight = delTextElement.offsetHeight;
          // 親要素に幅を設定
          substVerticalElement.style.width = `${delTextHeight}px`;
          if (addTextHeight > delTextHeight) {
            addTextElement.style.top = `${(-1 * addTextHeight) / 2 - delTextElement.offsetHeight / 2}px`;
          } else {
            addTextElement.style.top = `${(-1 * delTextHeight) / 2 - addTextElement.offsetHeight / 2}px`;
          }
        } else {
          addTextElement.style.top = `${(-1 * addTextElement.offsetHeight) / 2}px`;
        }
      }
    });

    // tei-subst-horizontalクラスを持つ要素の幅等の調整;
    const substHorizontalElements = rootElement.querySelectorAll(".tei-subst-horizontal");
    substHorizontalElements.forEach((substHorizontalElement) => {
      const delTextElement = substHorizontalElement.querySelector(".tei-del_text");
      const addTextElement = substHorizontalElement.querySelector(".tei-add");
      if (addTextElement) {
        const addTextWidth = addTextElement.offsetWidth;
        let delTextWidth = 0;
        if (delTextElement) {
          // tei-del_text の幅を取得
          delTextWidth = delTextElement.offsetWidth;
          // 親要素に幅を設定
          substHorizontalElement.style.width = `${delTextWidth}px`;
          if (addTextWidth > delTextWidth) {
            addTextElement.style.left = `${(-1 * addTextWidth) / 2 - delTextElement.offsetWidth / 2}px`;
          } else {
            addTextElement.style.left = `${(-1 * delTextWidth) / 2 - addTextElement.offsetWidth / 2}px`;
          }
        } else {
          addTextElement.style.left = `${(-1 * addTextElement.offsetWidth) / 2}px`;
        }
      }
    });

    // tei-note-verticalクラスを持つ要素の高さ等の調整;
    const noteVerticalElements = rootElement.querySelectorAll(".tei-note-vertical");
    noteVerticalElements.forEach((noteVerticalElement) => {
      const targetTextElement = noteVerticalElement.querySelector(".tei-note-vertical_target");
      const noteTextElement = noteVerticalElement.querySelector(".tei-note-vertical_text");
      const noteTextHeight = noteTextElement.offsetHeight;
      if (targetTextElement) {
        const lineHeight = parseFloat(window.getComputedStyle(noteVerticalElement).lineHeight);
        const targetTextElementWidth = targetTextElement.offsetWidth;
        // 行数を計算
        const lineCount = Math.ceil(targetTextElementWidth / lineHeight);
        let targetTextHeight = 0;
        // tei-note-vertical_target の高さを取得
        targetTextHeight = targetTextElement.offsetHeight;
        // 親要素に高さを設定
        if (noteTextHeight > targetTextHeight) {
          noteTextElement.style.top = `${(-1 * noteTextHeight) / 2 - targetTextElement.offsetHeight / 2}px`;
        } else {
          noteTextElement.style.top = `${(-1 * targetTextHeight) / 2 - noteTextElement.offsetHeight / 2}px`;
        }
        if (lineCount == 1) {
          noteVerticalElement.style.display = "inline-flex";
          noteVerticalElement.style.alignItems = "flex-end";
          noteVerticalElement.style.height = `${targetTextHeight + 3}px`;
          targetTextElement.style.whiteSpace = "nowrap";
        }
        if (lineCount >= 2) {
          noteTextElement.style.top = "0px";
          noteTextElement.style.right = "-2.6rem";
          noteTextElement.style.display = "block";
          targetTextElement.style.lineHeight = "2rem";
        }
      } else {
        noteVerticalElement.style.height = "0px";
        noteVerticalElement.style.display = "inline-block";
        noteTextElement.style.top = `${(-1 * noteTextElement.offsetHeight) / 2}px`;
      }
    });

    // tei-note-horizontalクラスを持つ要素の幅等の調整;
    const noteHorizontalElements = rootElement.querySelectorAll(".tei-note-horizontal");
    noteHorizontalElements.forEach((noteHorizontalElement) => {
      const targetTextElement = noteHorizontalElement.querySelector(".tei-note-horizontal_target");
      const noteTextElement = noteHorizontalElement.querySelector(".tei-note-horizontal_text");
      const noteTextWidth = noteTextElement.offsetWidth;
      if (targetTextElement) {
        const lineHeight = parseFloat(window.getComputedStyle(noteHorizontalElement).lineHeight);
        const targetTextElementHeight = targetTextElement.offsetHeight;
        // 行数を計算
        const lineCount = Math.ceil(targetTextElementHeight / lineHeight);
        let targetTextWidth = 0;
        // tei-note-horizontal_target の幅を取得
        targetTextWidth = targetTextElement.offsetWidth;
        // 親要素に幅を設定
        if (noteTextWidth > targetTextWidth) {
          noteTextElement.style.left = `${(-1 * noteTextWidth) / 2 - targetTextElement.offsetWidth / 2}px`;
        } else {
          noteTextElement.style.left = `${(-1 * targetTextWidth) / 2 - noteTextElement.offsetWidth / 2}px`;
        }
        if (lineCount == 1) {
          noteHorizontalElement.style.display = "inline-flex";
          noteHorizontalElement.style.alignItems = "flex-end";
          noteHorizontalElement.style.width = `${targetTextWidth + 3}px`;
          targetTextElement.style.whiteSpace = "nowrap";
        }
        if (lineCount >= 2) {
          noteTextElement.style.left = "0px";
          noteTextElement.style.bottom = "0px";
          noteTextElement.style.display = "block";
        }
      } else {
        noteHorizontalElement.style.width = "0px";
        noteHorizontalElement.style.display = "inline-block";
        noteTextElement.style.left = `${(-1 * noteTextElement.offsetWidth) / 2}px`;
      }
    });

    // tei-split_textクラスを持つ要素の高さ等の調整;
    const splitTextElements = rootElement.querySelectorAll(".tei-split_text");
    splitTextElements.forEach((splitTextElement) => {
      const rightTextElement = splitTextElement.querySelector(".tei-split_text-right");
      const leftTextElement = splitTextElement.querySelector(".tei-split_text-left");
      const rightText = rightTextElement.textContent;
      const leftText = leftTextElement.textContent;
      // 連続する空白を1つにまとめる
      const rightCleanedText = rightText.replace(/\s+/g, " ").trim();
      const leftCleanedText = leftText.replace(/\s+/g, " ").trim();
      const maxLength = Math.max(rightCleanedText.length, leftCleanedText.length);
      // maxLengthに応じてsplitTextElementの高さを指定、
      splitTextElement.style.height = `${maxLength * 0.5}rem`;
    });

    // tei-figureクラスを持つ要素の数字を横書き表示できるようにする;
    const figureElements = rootElement.querySelectorAll(".tei-figure");
    figureElements.forEach((figureElement) => {
      const figureText = figureElement.innerHTML;
      // 数字部分を<span>タグで囲む
      const modifiedText = figureText.replace(/\d+/g, (match) => {
        return `<span class="tei-figure_num">${match}</span>`;
      });
      // 要素に反映
      figureElement.innerHTML = modifiedText;
    });
  }
});
</script>
<template>
  <div class="p-3" v-html="output"></div>
</template>
