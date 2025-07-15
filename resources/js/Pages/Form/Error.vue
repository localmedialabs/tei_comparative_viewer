<script setup>
import { usePage } from "@inertiajs/vue3";
const props = defineProps({
  form: Object,
});

const form = props.form;

const page = usePage();
const flashMessage = page.props.flash.error || "";
const formattedMessage = flashMessage.replace(/\n/g, "<br>");
if (!formattedMessage) {
  form.post(route("viewer"));
}
</script>
<template>
  <div class="text-center mb-4">
    <div v-if="formattedMessage" class="text-danger" v-html="`${formattedMessage}想定の表示がされない可能性があります。`"></div>
  </div>
</template>
