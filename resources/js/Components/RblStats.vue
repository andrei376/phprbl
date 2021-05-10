<template>
    <div class="mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-3 bg-white border-b border-gray-200 divide-y-4 space-y-3 divide-fuchsia-300 position-relative">
                <div class="font-mono font-semibold text-blue-500">{{ this.__('STATS') }}</div>

                <div class="pt-1">
                {{ this.__('Server load')}}: {{ statsData.loadAvg[0] }}, {{ statsData.loadAvg[1] }}, {{ statsData.loadAvg[2] }}
                <br>
                {{ this.__('Date')}}: {{ statsData.date }}
                <br>
                <span class="rounded p-1" :class="statsData.cache ? 'text-green-700 bg-green-200' : 'text-red-700 bg-red-200'">{{ this.__('Cache') }}</span>
                <br>
                <inertia-link v-if="statsData.logCount > 0" :href="this.route('rbl.logs')" class="btn btn-sm btn-danger px-1 py-0 mt-2">{{ this.__('Check LOGS') }}</inertia-link>
                </div>

                <div class="pt-1">
                    <div class="text-center">{{ this.__('IPs in lists') }}</div>

                    <div v-for="(list, key) in statsData.lists4"><span style="min-width: 60px;display:inline-block;">{{ key }}:</span> <span class="font-bold">{{ list.count }}</span> <span class="rounded px-1 font-bold float-right" :class="list.sync ? 'text-green-700 bg-green-200' : 'text-red-700 bg-red-200'">sync</span></div>
                    <br>
                    <div v-for="(list, key) in statsData.lists6"><span style="min-width: 60px;display:inline-block;">{{ key }}:</span> <span class="font-bold">{{ list.count }}</span> <span class="rounded px-1 font-bold float-right" :class="list.sync ? 'text-green-700 bg-green-200' : 'text-red-700 bg-red-200'">sync</span></div>
                </div>

                <div class="pt-1">
                    <div class="text-center">{{ this.__('To check') }}</div>

                    <div class="w-6/12 inline-block">
                        <div v-for="(list, key) in statsData.lists4">
                        <span style="min-width: 60px;display:inline-block;">{{ key }}:</span>
                        <inertia-link v-if="list.check_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.check4', key)">{{ list.check_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.check_count }}</span>
                        </div>
                    </div>
                    <div class="w-6/12 inline-block text-right">
                        <div v-for="(list, key) in statsData.lists6">
                        {{ key }}:
                        <inertia-link v-if="list.check_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.check6', key)">{{ list.check_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.check_count }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-1">
                    <div class="text-center">{{ this.__('Missing netname') }}</div>

                    <div class="w-6/12 inline-block">
                        <div v-for="(list, key) in statsData.lists4">
                        <span style="min-width: 60px;display:inline-block;">{{ key }}:</span>
                        <inertia-link v-if="list.netname_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.netname4', key)">{{ list.netname_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.netname_count }}</span>
                        </div>
                    </div>

                    <div class="w-6/12 inline-block text-right">
                        <div v-for="(list, key) in statsData.lists6">
                        {{ key }}:
                        <inertia-link v-if="list.netname_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.netname6', key)">{{ list.netname_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.netname_count }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-1">
                    <div class="text-center">{{ this.__('To delete') }}</div>

                    <div class="w-6/12 inline-block">
                        <div v-for="(list, key) in statsData.lists4">
                        <span style="min-width: 60px;display:inline-block;">{{ key }}:</span>
                        <inertia-link v-if="list.delete_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.delete4', key)">{{ list.delete_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.delete_count }}</span>
                        </div>
                    </div>

                    <div class="w-6/12 inline-block text-right">
                        <div v-for="(list, key) in statsData.lists6">
                        {{ key }}:
                        <inertia-link v-if="list.delete_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.delete6', key)">{{ list.delete_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.delete_count }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-1">
                    <div class="text-center">{{ this.__('Inactive') }}</div>

                    <div class="w-6/12 inline-block">
                        <div v-for="(list, key) in statsData.lists4">
                        <span style="min-width: 60px;display:inline-block;">{{ key }}:</span>
                        <inertia-link v-if="list.inactive_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.inactive4', key)">{{ list.inactive_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.inactive_count }}</span>
                        </div>
                    </div>

                    <div class="w-6/12 inline-block text-right">
                        <div v-for="(list, key) in statsData.lists6">
                        {{ key }}:
                        <inertia-link v-if="list.inactive_count > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('rbl.inactive6', key)">{{ list.inactive_count }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ list.inactive_count }}</span>
                        </div>
                    </div>
                </div>
                <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
            </div>
<!--            <pre>{{ statsData }}</pre>-->
        </div>
    </div>
</template>


<script>
    export default {

        data(){
            return {
                isLoading: false,
                statsData: {
                    'loadAvg': ['0', '1', '2'],
                    'date': '',
                    'cache': false,
                    'logCount': 0,
                    'lists4': {
                        "White": {
                            "count": 0,
                            "sync": false,
                            "check_count": 0,
                            "netname_count": 0,
                            "delete_count": 0,
                            "inactive_count": 0
                        },
                        "Grey": {
                            "count": 0,
                            "sync": false,
                            "check_count": 0,
                            "netname_count": 0,
                            "delete_count": 0,
                            "inactive_count": 0
                        },
                        "Black": {
                            "count": 0,
                            "sync": false,
                            "check_count": 0,
                            "netname_count": 0,
                            "delete_count": 0,
                            "inactive_count": 0
                        }
                    },
                    "lists6": {
                        "White6": {
                            "count": 0,
                            "sync": false,
                            "check_count": 0,
                            "netname_count": 0,
                            "delete_count": 0,
                            "inactive_count": 0
                        },
                        "Grey6": {
                            "count": 0,
                            "sync": false,
                            "check_count": 0,
                            "netname_count": 0,
                            "delete_count": 0,
                            "inactive_count": 0
                        },
                        "Black6": {
                            "count": 0,
                            "sync": false,
                            "check_count": 0,
                            "netname_count": 0,
                            "delete_count": 0,
                            "inactive_count": 0
                        }
                    }
                }
            }
        },

        methods: {
            getStats: function() {
                this.isLoading = true;
                axios.get(this.route('stats.rbl')).then(function(response){
                    this.statsData = response.data;
                    this.isLoading = false;
                }.bind(this)).catch(errors => {
                    if (errors.response.status == 401 && errors.response.statusText == 'Unauthorized') {
                        window.location = this.route('login');
                    }
                    this.isLoading = false;
                });
            }
        },
        mounted: function() {
            this.getStats();
            eventBus.on('refreshRblStats', () => {
                this.getStats();
            });
        },
        cron: {
            time: 30000,
            method: 'getStats'
        },
        beforeUnmount() {
            this.$cron.stop('getStats');
        }
    }
</script>

