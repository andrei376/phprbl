<template>
<breeze-authenticated-layout>
    <div class="pt-2 pb-12">
        <div class="w-6/12 mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="update" novalidate autocomplete="off">
                        <!-- Name -->
                        <div class="mt-4">
                            <breeze-label for="name" field="name" :value="this.__('[Variable name]') + ' *'" />

                            <breeze-input id="name" field="name" class="block mt-1 w-full" type="text" name="name" v-model="form.name" autofocus required />
                        </div>

                        <!-- Value -->
                        <div class="mt-4">
                            <breeze-label for="value" field="value" :value="this.__('[Value]') + ' *'" />

                            <breeze-input id="value" field="value" class="block mt-1 w-full" type="text" name="value" v-model="form.value" required />
                        </div>


                        <div class="flex items-center justify-between mt-4">
                            <inertia-link class="btn btn-primary" :href="this.route('setup.index')">{{ this.__('[Back]') }}</inertia-link>

                            <button @click="$event.target.blur();" type="submit" class="btn btn-success" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ this.__('[Save]') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</breeze-authenticated-layout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'
    import BreezeInput from '@/Components/Input'
    import BreezeLabel from '@/Components/Label'

    export default {
        name: "EditVar",
        components: {
            BreezeAuthenticatedLayout,
            BreezeInput,
            BreezeLabel
        },

        props: [
            'setupvar',
            'isEdit'
        ],

        mounted() {
            let input = document.querySelector('[autofocus]');
            if (input) {
                input.focus()
            }
        },

        data() {
            return {
                form: this.$inertia.form({
                    name: this.isEdit ? this.setupvar.name : null,
                    value: this.isEdit ? this.setupvar.value: null
                })
            }
        },

        methods: {
            focusError() {
                let el = document.querySelector("input.border-red-700");

                el.focus();
            },
            update() {
                if (this.isEdit) {
                    this.form.put(this.route('setup.updatevar', this.setupvar.id), {
                        onError: () => {
                            this.focusError();
                        }
                    });
                } else {
                    this.form.post(this.route('setup.storevar'), {
                        onError: () => {
                            this.focusError();
                        }
                    });
                }
            }
        }
    }
</script>
