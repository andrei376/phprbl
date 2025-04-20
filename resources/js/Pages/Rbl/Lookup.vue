<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="getWhois" novalidate autocomplete="off">
                            <!-- Name -->
                            <div class="mt-4">
                                <breeze-label for="ip" field="ip" :value="this.__('Ip') + ' *'" />

                                <breeze-input id="ip" field="ip" class="block mt-1 w-full" type="text" name="ip" v-model="form.ip" autofocus required />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" @click="$event.target.blur();" class="btn btn-success" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Lookup') }}
                                </button>
                            </div>
                        </form>

                        <div id="1showDns" v-if="flashData && (flashData.result !== undefined) && (flashData.result.dns !== undefined)">
                            <table class="table table-striped table-bordered table-sm col-6">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="w-2/12">{{ this.__('List') }}</th>
                                        <th class="w-2/12">{{ this.__('Found') }}</th>
                                        <th class="w-8/12">{{ this.__('IP') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, list) in flashData.result.dns">
                                        <td>{{ list }}</td>
                                        <td>
                                            <span v-if="!row.found" class="badge bg-danger mr-5" style="min-width: 4rem;">{{ row.found }}</span>
                                            <span v-else class="badge bg-success mr-5" style="min-width: 4rem;">{{ row.found }}</span>
                                        </td>
                                        <td>{{ row.ip }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div id="5showDoubledIp" v-if="flashData && (flashData.result !== undefined) && (flashData.result.multiple !== undefined)">
                            <row-table
                                class="mt-4"
                                v-if="flashData.result.multiple.length > 0"
                                :table-data="flashData.result.multiple"
                                />
                        </div>

<!--                        <pre v-if="flashData.result !== undefined">
                            {{ flashData.result }}
                        </pre>-->
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
import RowTable from "./RowTable";

export default {
    components: {
        BreezeAuthenticatedLayout,
        BreezeInput,
        BreezeLabel,
        RowTable
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
                ip: null
            })
        }
    },

    methods: {
        focusError() {
            let el = document.querySelector("input.border-red-700");

            el.focus();
        },
        getWhois() {
            // noinspection JSUnusedLocalSymbols
            this.form.post(this.route('rbl.getLookup'), {
                onError: errors => {
                    this.focusError();
                }
            });
        }
    }
}
</script>
