<script setup>
import BaseHeader from "@/Layouts/Partials/Header.vue";
const emit = defineEmits(["headerHeight"]);

const updateHeaderHeight = (headerHeight) => {
  document.documentElement.style.setProperty("--header-height", `${headerHeight}px`);
};
</script>

<template>
  <div id="page-container">
    <BaseHeader @headerHeight="updateHeaderHeight">
      <slot name="header"></slot>
    </BaseHeader>

    <div id="main-container">
      <slot></slot>
    </div>
  </div>
</template>
<style lang="scss">
:root {
  --header-height: 0px; /* 初期値 */
}
</style>
