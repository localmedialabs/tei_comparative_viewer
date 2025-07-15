<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import Form from "@/Pages/Form/Form.vue";
import Error from "@/Pages/Form/Error.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  errors: Object,
});

const form = useForm({
  tei: null, // TEIファイルの内容を格納する変数
  tei_url: "", // TEIファイルのURLを格納する変数
});

const isConfirm = ref(false);

const onConfirm = () => {
  form.post(route("validate"), {
    onSuccess: () => {
      switchConfirm(), (form.tei_url = form.tei ? URL.createObjectURL(form.tei) : "");
    },
  });
};

const switchConfirm = () => {
  if (form.processing || form.hasErrors) {
    isConfirm.value = false;
    return;
  }
  isConfirm.value = !isConfirm.value;
};
</script>

<template>
  <MainLayout>
    <main>
      <div class="container mt-5">
        <template v-if="form.processing">
          <div class="text-center">
            <p class="mt-2">処理中...</p>
          </div>
        </template>
        <template v-else>
          <template v-if="!isConfirm">
            <Form :form="form" :parent_props="props" />
            <div class="text-center">
              <button type="button" @click="onConfirm" :disabled="form.processing" class="btn btn-primary fs-5 px-5">登録する</button>
            </div>
          </template>
          <template v-else>
            <Error :form="form" />
            <div class="d-flex gap-3 justify-content-center">
              <button type="button" @click="isConfirm = false" :disabled="form.processing" class="btn btn-secondary fs-5 px-5">戻る</button>
              <button type="button" @click="form.post(route('viewer'))" :disabled="form.processing" class="btn btn-primary fs-5 px-5">登録する</button>
            </div>
          </template>
        </template>
      </div>
    </main>
  </MainLayout>
</template>
