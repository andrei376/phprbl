<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="w-6/12 mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-3 bg-white border-b border-gray-200">
<!--                        <pre>{{ form }}</pre>-->
                        <form @submit.prevent="save" novalidate autocomplete="off">

                            <!-- ip -->
                            <div class="mt-4">
                                <breeze-label for="ips" field="ips" :value="this.__('[IPs]') + ' *'" />

                                <text-area id="ips" field="ips" class="block mt-1 w-full" name="ips" v-model="form.ips" rows="15" autofocus required />
                            </div>

                            <!-- rbl type -->
                            <div class="mt-4 flex items-center justify-start">

                                <div class="mr-1">{{ this.__('[RBL List]') }}: </div>

                                <div v-for="(list, index) in lists" class="ml-6">
                                    <breeze-radio :id="'list' + index" name="list" field="list" class="block mx-auto" v-model="form.list" :value="list" required />

                                    <breeze-label :for="'list' + index" field="list" :value="list" class="mt-1" />
                                </div>

                            </div>
                            <div v-for="error in this.$page.props.errors.list" class="text-red-700 text-sm block">{{ error }}</div>


                            <div class="flex items-center justify-end mt-4">
                                <button @click="$event.target.blur();" class="btn btn-success" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('[Save]') }}
                                </button>
                            </div>
                        </form>

                        <div v-for="error in this.$page.props.errors.eachIp" class="text-red-700 text-sm block" v-html="error[0]"></div>


                        <br>
                        <div v-html="resMsg"></div>

                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'
    import BreezeButton from '@/Components/Button'
    import BreezeInput from '@/Components/Input'
    import BreezeRadio from '@/Components/Radio'
    import BreezeLabel from '@/Components/Label'
    import TextArea from '@/Components/Textarea'

    export default {
        components: {
            BreezeAuthenticatedLayout,
            BreezeButton,
            BreezeInput,
            BreezeRadio,
            BreezeLabel,
            TextArea
        },

        mounted() {
            let input = document.querySelector('[autofocus]');
            if (input) {
                input.focus()
            }
        },

        props: ['lists', 'resMsg', 'flash'],

        data() {
            return {
                form: this.$inertia.form({
                    ips: null,
                    list: null
                })
            }
        },

        methods: {
            focusError() {
                let el = document.querySelector("input.border-red-700");

                if (el !== null) {
                    el.focus();
                }
            },
            save() {
                this.form.post(this.route('v4.save'), {
                    onSuccess: () => {
                        eventBus.emit('refreshRblStats');
                    },
                    onError: errors => {
                        for (const prop in errors) {

                            if (prop.startsWith("eachIp.")) {
                                if (this.$page.props.errors['eachIp'] === undefined) {
                                    this.$page.props.errors['eachIp'] = [];
                                }

                                this.$page.props.errors['eachIp'].push(errors[prop]);
                            }
                        }

                        this.focusError();
                    }
                });
            }
        }
    }
</script>
