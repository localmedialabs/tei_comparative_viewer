<script setup>
import { ref, onMounted } from "vue";

const emit = defineEmits(["headerHeight"]);
const header = ref(null);

onMounted(() => {
  const updateHeight = () => {
    emit("headerHeight", header.value.clientHeight);
  };

  window.addEventListener("load", updateHeight);
});
</script>

<template>
  <header ref="header" class="header sticky-top bg-white align-content-center">
    <div class="container-fluid">
      <h1 class="fs-4 fw-bold mb-0"><a href="/" class="text-black" style="text-decoration: none;">TEI/XML テキスト対象ビューワー</a></h1>
    </div>
  </header>
</template>

<style lang="scss" scoped>
.header {
  height: 64px;
  z-index: 1001;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
}
</style>
