<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-6 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="getAs" novalidate autocomplete="off">
                            <!-- Name -->
                            <div class="mt-4">
                                <breeze-label for="asn" field="asn" :value="this.__('AS number') + ' *'" />

                                <breeze-input id="asn" field="asn" class="block mt-1 w-full" type="text" name="asn" v-model="form.asn" autofocus required />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" @click="$event.target.blur();" class="btn btn-success" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Get AS Ips') }}
                                </button>
                            </div>
                        </form>

                        <div v-for="prefix in flashData.result">
                            {{ prefix }}
                        </div>

                        <pre v-if="flashData">{{ flashData.result }}</pre>
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
        components: {
            BreezeAuthenticatedLayout,
            BreezeInput,
            BreezeLabel
        },
        mounted() {
            let input = document.querySelector('[autofocus]');
            if (input) {
                input.focus()
            }
        },
        props: ['flashData'],

        data() {
            return {
                form: this.$inertia.form({
                    asn: null
                })
            }
        },

        methods: {
            focusError() {
                let el = document.querySelector("input.border-red-700");

                el.focus();
            },
            getAs() {
                // noinspection JSUnusedLocalSymbols
                this.form.post(this.route('rbl.getAs'), {
                    onError: errors => {
                        this.focusError();
                    }
                });
            }
        }
    }
</script>
