<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="position-fixed bg-light shadow border-bottom" style="z-index: 4;">
                            <h4 class="m-0">{{ this.__('List') }}: {{ list }}, IP: <span :class="rangeInfo === whoisData.range ? 'text-success' : 'text-danger'">{{ cidrInfo }}{{ hostnameInfo }}</span></h4>
                        </div>
                        <br>
                        <br>

                        <div id="1showIpInfo" class="position-relative">
                            <table class="table table-striped table-bordered table-sm">
                                <tbody>
                                    <tr class="">
                                        <th scope="row" class="col-1 table-dark text-right" style="width: 10%;">mask</th>
                                        <td class="col-11">{{ ipInfo.mask }} ({{ this.__("total hosts") }} = 2^(32-{{ ipInfo.mask }})=
                                            <span class="font-weight-bolder">{{ Intl.NumberFormat('ro-RO').format(Math.pow(2, (32 - ipInfo.mask))) }}</span>)
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">inetnum</th>
                                        <td>{{ ipInfo.inetnum }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">netname</th>
                                        <td>{{ ipInfo.netname }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">orgname</th>
                                        <td>{{ ipInfo.orgname }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">country</th>
                                        <td>{{ ipInfo.country }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">geoipcountry</th>
                                        <td>{{ ipInfo.geoipcountry }}  ({{ geoCountry }})</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">delete</th>
                                        <td>
                                            <span v-if="ipInfo.delete" class="badge bg-danger mr-5" style="min-width: 4rem;">{{ ipInfo.delete }}</span>
                                            <span v-else class="badge bg-success mr-5" style="min-width: 4rem;">{{ ipInfo.delete }}</span>
                                            <inertia-link class="badge btn btn-secondary mr-2" :href="this.route('rbl.toggle4', {'id': ipInfo.id, 'list': list, 'field': 'delete'})">{{ this.__('Change') }}</inertia-link>
                                            ({{ this.__('remove from db: delete=1, checked=1, active=0') }})
                                            <button @click="deleteRow(ipInfo.id, list)" type="button" class="badge bg-danger ml-2">{{ this.__('[Mark to delete]') }}</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">active</th>
                                        <td>
                                            <span v-if="ipInfo.active" class="badge bg-success mr-5" style="min-width: 4rem;">{{ ipInfo.active }}</span>
                                            <span v-else class="badge bg-danger mr-5" style="min-width: 4rem;">{{ ipInfo.active }}</span>
                                            <inertia-link class="badge btn btn-secondary mr-2" :href="this.route('rbl.toggle4', {'id': ipInfo.id, 'list': list, 'field': 'active'})">{{ this.__('Change') }}</inertia-link>
                                            ({{ this.__('exported to dns: active=1, delete=0') }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">date_added</th>
                                        <td :class="ipInfo.date_added ? 'text-success' : 'text-danger'">
                                            {{ ipInfo.date_added_format }}  ({{ ipInfo.date_added_ago }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">last_check</th>
                                        <td :class="ipInfo.last_check ? 'text-success' : 'text-danger'">
                                            {{ ipInfo.last_check_format }}  <span v-if="ipInfo.last_check_ago">({{ ipInfo.last_check_ago }})</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="col-auto table-dark text-right">checked</th>
                                        <td>
                                            <span v-if="ipInfo.checked" class="badge bg-success mr-5" style="min-width: 4rem;">{{ ipInfo.checked }}</span>
                                            <span v-else class="badge bg-danger mr-5" style="min-width: 4rem;">{{ ipInfo.checked }}</span>
                                            <inertia-link class="badge btn btn-secondary mr-2" :href="this.route('rbl.toggle4', {'id': ipInfo.id, 'list': list, 'field': 'checked'})">{{ this.__('Change') }}</inertia-link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="checkLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
                        </div>

                        <div id="2showHitsInfo" class="w-1/3">
                            <dns-hits :list="list" :id="ipInfo.id" />
                        </div>

                        <div id="3showWhoisData" class="position-relative">
                            <table class="table table-striped table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="w-2/12">{{ this.__('Whois') }}</th>
                                        <th class="w-2/12">date</th>
                                        <th class="w-3/12">inetnum</th>
                                        <th class="w-2/12">netname</th>
                                        <th class="w-1/12">country</th>
                                        <th class="w-2/12">orgname</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr></tr>
                                    <tr class="text-success">
                                        <td>new/current</td>
                                        <td>{{ whoisData.date }}</td>
                                        <td>{{ whoisData.inetnum }}</td>
                                        <td>{{ whoisData.netname }}</td>
                                        <td>{{ whoisData.country }}</td>
                                        <td>{{ whoisData.orgname }}</td>
                                    </tr>
                                    <tr>
                                        <td :class="checkWhois() ? 'text-success' : 'text-danger'">
                                            <span v-if="checkWhois()">{{ this.__('now in db/same') }}</span>
                                            <span v-else v-html="this.__('now in db/different<br>should update')"></span>
                                        </td>
                                        <td>{{ ipInfo.last_check_format }}</td>
                                        <td :class="whoisData.inetnum !== ipInfo.inetnum ? 'text-danger' : 'text-success'">{{ ipInfo.inetnum ?? 'empty' }}</td>
                                        <td :class="whoisData.netname !== ipInfo.netname ? 'text-danger' : 'text-success'">{{ ipInfo.netname ?? 'empty' }}</td>
                                        <td :class="whoisData.country !== ipInfo.country ? 'text-danger' : 'text-success'">{{ ipInfo.country ?? 'empty' }}</td>
                                        <td :class="whoisData.orgname !== ipInfo.orgname ? 'text-danger' : 'text-success'">{{ ipInfo.orgname ?? 'empty' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="whoisLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
                            <a @click="showWhois = !showWhois" href="#">{{ this.__('show full whois output') }}</a>
                            <div v-show="showWhois" class="whitespace-pre position-relative bg-light" style="z-index: 2;">
                                <div class="text-primary">
                                    {{ whoisData.output }}
                                </div>
                                <div class="text-danger position-fixed bg-light" style="top: 350px; left: 1100px;">
                                    {{ this.__('Information used:') }}<br>
                                    Inetnum: {{ whoisData.inetnum }}<br>
                                    Range: {{ whoisData.range }}<br>
                                    Netname: {{ whoisData.netname }}<br>
                                    Orgname: {{ whoisData.orgname }}<br>
                                    Country: {{ whoisData.country }}
                                </div>
                            </div>
                        </div>

                        <div id="4showDbBtns" class="mt-1">
                            <div>
                                <button type="button" @click="$event.target.blur(); this.forceWhois();" :class="{ 'opacity-25': whoisLoading }" :disabled="whoisLoading" class="btn btn-sm btn-outline-secondary">
                                    <span v-if="whoisLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Force get new whois') }}
                                </button>

                                <button type="button" @click="$event.target.blur(); this.updateWhois();" :class="{ 'opacity-25': whoisLoading }" :disabled="whoisLoading" class="ml-5 btn btn-sm btn-outline-secondary">
                                    <span v-if="whoisLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Update whois') }}
                                </button>

                                <button type="button" @click="$event.target.blur(); this.updateLastCheck();" :class="{ 'opacity-25': checkLoading }" :disabled="checkLoading" class="ml-5 btn btn-sm btn-outline-secondary">
                                    <span v-if="checkLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Update last check') }}
                                </button>
                            </div>
                            <div class="mt-2">
                                <breeze-label for="replace" field="replace" class="mb-0" :value="this.__('Replace ip with')" />
                                <div class="form-row">
                                    <div class="col-2">
                                        <input id="replace" class="form-control form-control-sm mt-0" type="text" name="replace" v-model="whoisData.range" required />
                                    </div>
                                </div>

                                <button type="button" @click="$event.target.blur(); this.updateIp();" :class="{ 'opacity-25': ipLoading }" :disabled="ipLoading" class="btn btn-sm btn-outline-secondary mt-1">
                                    <span v-if="ipLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Replace ip') }}
                                </button>
                                <button type="button" @click="$event.target.blur(); this.updateIp24();" :class="{ 'opacity-25': ipLoading }" :disabled="ipLoading" class="btn btn-sm btn-outline-secondary mt-1 ml-5">
                                    <span v-if="ipLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('Replace ip with') }} {{ this.newIp24 }}
                                </button>
                            </div>
                        </div>

                        <div id="5showDoubledIp">
                            <row-table
                                class="mt-4"
                                v-if="multiple.length > 0"
                                :table-data="multiple"
                                :table-name="this.__('multiple IP') + ' (' + multiple.length + ')'"
                                :table-name-link="this.route('rbl.many4', {'id': ipInfo.id, 'list': list, 'cidr': 1})" />
                        </div>

                        <div id="6showOther24Ip">
                            <row-table
                                class="mt-4"
                                v-if="other24.length > 0"
                                :table-data="other24"
                                :table-name="this.__('Other IP in /24') + ' ('+other24.length+')'"
                            />
                        </div>

                        <div id="7showOther16Ip">
                            <row-table
                                class="mt-4"
                                v-if="other16.length > 0"
                                :table-data="other16"
                                :table-name="this.__('Other IP in /16') + ' ('+other16.length+')'"
                                :table-name-link="this.route('rbl.many4', {'id': ipInfo.id, 'list': list})"
                            />
                        </div>

                        <div id="8showChangeList" class="mt-1 mb-4">
                            <breeze-label for="move" field="move" class="mb-0" :value="this.__('Move from :list to list:', {'list': this.list})" />

                            <div class="form-row align-items-center">
                                <div class="col-2">
                                    <select class="form-control form-control-sm" id="move" name="move" v-model="moveList">
                                        <option v-for="list in moveLists" v-bind:value="list">{{ list }}</option>
                                    </select>
                                </div>
                            </div>


                            <button type="button" @click="$event.target.blur(); this.moveIp();" :class="{ 'opacity-25': moveLoading }" :disabled="moveLoading" class="btn btn-sm btn-outline-secondary mt-1">
                                <span v-if="moveLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ this.__('Move ip') }}
                            </button>
                        </div>

                        <div id="9showLogsIp">
                            <log-hits :list="list" :id="ipInfo.id" :search-ip="ipInfo.iplong" />
                        </div>

                        <div id="11elastic">
                            <elastic :iplong="ipInfo.iplong" :mask="ipInfo.mask" />
                        </div>

<!--                        <div id="10syslog">
                            <syslog :iplong="ipInfo.iplong" :mask="ipInfo.mask" />
                        </div>-->
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
import DnsHits from "./DnsHits";
import Button from "../../Components/Button";
import RowTable from "./RowTable";
import LogHits from "./LogHits";
import Syslog from "@/Pages/Rbl/Syslog";
import Elastic from "@/Components/Elastic";

export default {
    components: {
        Elastic,
        Button,
        BreezeInput,
        BreezeLabel,
        BreezeAuthenticatedLayout,
        DnsHits,
        RowTable,
        LogHits,
        Syslog
    },

    props: [
        'ipInfo',
        'hostnameInfo',
        'cidrInfo',
        'geoCountry',
        'list',
        'whoisData',
        'rangeInfo',
        'moveLists',
        'multiple',
        'other24',
        'other16'
    ],
    data() {
        return {
            moveList: '',
            showWhois: false,
            whoisLoading: false,
            checkLoading: false,
            ipLoading: false,
            moveLoading: false
        }
    },
    computed: {
        newIp24() {
            let newip =   this.ipInfo.ip1 + '.'
                        + this.ipInfo.ip2 + '.'
                        + this.ipInfo.ip3 + '.'
                        + '0/24';

            return newip;
        }
    },

    methods: {
        checkWhois() {
            return (this.$page.props.whoisData.inetnum === this.$page.props.ipInfo.inetnum &&
                this.$page.props.whoisData.netname === this.$page.props.ipInfo.netname &&
                this.$page.props.whoisData.country === this.$page.props.ipInfo.country &&
                this.$page.props.whoisData.orgname === this.$page.props.ipInfo.orgname);
        },
        forceWhois() {
            this.whoisLoading = true;
            let url = this.route('update.show4', {
                id: this.ipInfo.id,
                list: this.list,
                forceWhois: true
            });

            axios.post(url).then(function(response){
                this.$page.props.whoisData = response.data;
                this.whoisLoading = false;
            }.bind(this)).catch(() => this.whoisLoading = false);
        },
        updateWhois() {
            this.whoisLoading = true;
            this.checkLoading = true;
            let url = this.route('update.show4', {
                id: this.ipInfo.id,
                list: this.list,
                updateWhois: true
            });

            axios.post(url).then(function(response){
                this.$noty.success("Information saved.");
                this.$page.props.ipInfo = response.data;

                eventBus.emit('refreshRblStats');
                this.whoisLoading = false;
                this.checkLoading = false;
            }.bind(this)).catch(() => this.whoisLoading = false);
        },
        updateLastCheck() {
            this.checkLoading = true;
            let url = this.route('update.show4', {
                id: this.ipInfo.id,
                list: this.list,
                updateLastCheck: true
            });

            axios.post(url).then(function (response) {
                this.$noty.success("Information saved.");
                this.$page.props.ipInfo = response.data;

                eventBus.emit('refreshRblStats');
                this.checkLoading = false;
            }.bind(this)).catch(() => this.checkLoading =false);
        },
        updateIp24() {
            this.ipLoading = true;
            let url = this.route('update.show4', {
                id: this.ipInfo.id,
                list: this.list,
                updateIp: true
            });

            axios.post(url, { 'newIp': this.newIp24 }).then(function () {
                window.location = this.route('rbl.show4', {
                    id: this.ipInfo.id,
                    list: this.list
                });

                this.ipLoading = false;
            }.bind(this)).catch((error) => {
                if (error.response.data.error) {
                    this.$noty.error(error.response.data.error, {
                        modal: true
                    });
                }

                this.ipLoading = false;
            });
        },
        updateIp() {
            this.ipLoading = true;
            let url = this.route('update.show4', {
                id: this.ipInfo.id,
                list: this.list,
                updateIp: true
            });

            axios.post(url, { 'newIp': this.whoisData.range }).then(function () {
                window.location = this.route('rbl.show4', {
                    id: this.ipInfo.id,
                    list: this.list
                });

                this.ipLoading = false;
            }.bind(this)).catch((error) => {
                if (error.response.data.error) {
                    this.$noty.error(error.response.data.error, {
                        modal: true
                    });
                }

                this.ipLoading = false;
            });
        },
        moveIp() {
            this.moveLoading = true;
            let url = this.route('update.show4', {
                id: this.ipInfo.id,
                list: this.list,
                moveIp: true
            });


            axios.post(url, { 'moveList': this.moveList }).then(function (response) {
                window.location = response.data;

                this.moveLoading = false;
            }.bind(this)).catch(error => {
                if (error.response.data.error) {
                    this.$noty.error(error.response.data.error, {
                        modal: true
                    });
                }

                this.moveLoading = false;
            });
        },
        deleteRow(id, list) {
            this.checkLoading = true;
            if (!confirm(this.__('[Are you sure you want to mark for deletion?]'))) return;

            axios.delete(this.route("rbl.destroy", {id: id, list: list})).then(function (response) {
                this.$noty.success("Information saved.");
                this.$page.props.ipInfo = response.data;

                eventBus.emit('refreshRblStats');

                this.checkLoading = false;
            }.bind(this)).catch(() => {
                this.$noty.error(this.__('[Error saving information.]'), {
                    modal: true
                });
            });
        }
    }
}
</script>
