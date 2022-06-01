<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="table table-striped table-bordered table-sm">
                            <thead class="table-dark">
                                <tr class="d-flex">
                                    <th scope="col" class="col-3">{{ this.__('List') }}</th>
                                    <th scope="col" class="col-9">{{ this.__('Rows in db') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(count, list) in data" class="d-flex">
                                <td class="col-3">{{ list }}</td>
                                <td class="col-9">
                                    {{ count }}
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <table class="table table-striped table-bordered table-sm">
                            <thead class="table-dark">
                                <tr class="d-flex">
                                    <th scope="col" class="col-4">{{ this.__('[Mail log]') }}</th>
                                    <th scope="col" class="col-8">{{ this.__('[Rows in log]') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="d-flex">
                                    <td class="col-4">Mongo</td>
                                    <td class="col-8">{{ mongo }}</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col-4">Elastic</td>
                                    <td class="col-8">{{ elastic }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <simple-table
                            class="mt-5"
                            :table-data="classes"
                            :table-name="this.__('[Multiple classes in /16]')"
                            :columns="columns"
                        />

                        <simple-table
                            class="mt-5"
                            :table-data="ips"
                            :table-name="this.__('[Multiple ip in /24]')"
                            :columns="columns"
                        />

<!--                        <pre>{{ data }}</pre>-->
                        laravelVersion: {{ laravelVersion }}
                        <br>

                        phpVersion: {{ phpVersion }}
                        <br>
                        bootstrap: {{ bootstrapVersion }}
                        <br>
                        vue: {{ vueVersion() }}
                        <br>
                        jQuery: {{ jqueryVersion }}
                        <br>
                        <div v-if="stats.version !== undefined" class="mt-5">
                            <memcache-stats :stats="stats" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'
    import * as Vue from 'vue';
    import SimpleTable from "@/Pages/Rbl/SimpleTable";
    import MemcacheStats from "@/Components/MemcacheStats";

    export default {
        components: {
            BreezeAuthenticatedLayout,
            SimpleTable,
            MemcacheStats
        },

        props: [
            'data',
            'ips',
            'classes',
            'mongo',
            'elastic',
            'laravelVersion',
            'phpVersion',
            'stats'
        ],

        data() {
            return {
                bootstrapVersion: $.fn.tooltip.Constructor.VERSION,
                jqueryVersion: $.fn.jquery,
                columns: [
                    {
                        'name': this.__('[ip1]'),
                        'showField': 'ip1',
                        'class': 'w-1/12'
                    },
                    {
                        'name': this.__('[ip2]'),
                        'showField': 'ip2',
                        'class': 'w-1/12'
                    },
                    {
                        'name': this.__('[ip3]'),
                        'showField': 'ip3',
                        'class': 'w-1/12'
                    },
                    {
                        'name': this.__('[ip4]'),
                        'showField': 'ip4',
                        'class': 'w-1/12'
                    },
                    {
                        'name': this.__('[inetnum]'),
                        'showField': 'inetnum',
                        'class': 'w-6/12',
                    },
                    {
                        'name': this.__('[List]'),
                        'showField': 'list',
                        'class': 'w-2/12',
                    }
                ]
            }
        },
        methods: {
            vueVersion: function(){
                return Vue.version;
            }
        },
        created() {
            /*// Basic alert
            this.$noty.show("Hello world!")

// Success notification
            this.$noty.success("Your profile has been saved!")

// Error message
            this.$noty.error("Oops, something went wrong!")

// Warning
            this.$noty.warning("Please review your information.")

// Basic alert
            this.$noty.info("New version of the app is available!")*/
        }
    }
</script>
