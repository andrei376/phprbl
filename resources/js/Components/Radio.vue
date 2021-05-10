<template>
    <input type="radio" :value="value" v-model="proxyChecked"
        :checked="value === modelValue"
           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
</template>

<script>
export default {
    emits: ['update:modelValue'],

    props: {
        modelValue: {
            default: null
        },

        value: {
            default: null,
        },
    },

    computed: {
        proxyChecked: {
            get() {
                return this.value;
            },

            set(val) {
                this.$emit("update:modelValue", val);
            },
        },
    },
}
</script>
