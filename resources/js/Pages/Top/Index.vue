<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <button v-for="topBy in this.topList" @click="$event.target.blur(); this.changeTop(topBy);" type="button" class="btn mr-4" :class="topBy === showTop ? 'btn-primary' : 'btn-outline-secondary'">
                            {{ topBy }}
                        </button>
                        <div class="mt-5"></div>
                        <button v-for="list in this.lists" @click="$event.target.blur(); this.changeList(list);" type="button" class="btn mr-5"  :class="list === showList ? 'btn-primary' : 'btn-outline-secondary'">
                            {{ list }}
                        </button>
                        <button v-for="list in this.lists6" @click="$event.target.blur(); this.changeList(list);" type="button" class="btn mr-5"  :class="list === showList ? 'btn-primary' : 'btn-outline-secondary'">
                            {{ list }}
                        </button>

                        <div v-if="showTop !== null && showList !== null" class="mt-5">
                            <div v-if="showTop === 'Show by ip1'">
                                <top-ip1 v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by country'">
                                <top-country v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by hits'">
                                <top-hits v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by netname'">
                                <top-netname v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by inetnum'">
                                <top-inetnum v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by orgname'">
                                <top-orgname v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by date added'">
                                <top-date-added v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by last hit'">
                                <top-last-hit v-bind:list="showList" />
                            </div>
                            <div v-if="showTop === 'Show by last check'">
                                <top-last-check v-bind:list="showList" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'
    import Button from "@/Components/Button";
    import TopIp1 from "@/Pages/Top/TopIp1";
    import TopCountry from "@/Pages/Top/TopCountry";
    import TopNetname from "@/Pages/Top/TopNetname";
    import TopInetnum from "@/Pages/Top/TopInetnum";
    import TopOrgname from "@/Pages/Top/TopOrgname";
    import TopDateAdded from "@/Pages/Top/TopDateAdded";
    import TopLastCheck from "@/Pages/Top/TopLastCheck";
    import TopLastHit from "@/Pages/Top/TopLastHit";
    import TopHits from "@/Pages/Top/TopHits";

    export default {
        components: {
            Button,
            BreezeAuthenticatedLayout,
            TopIp1,
            TopCountry,
            TopNetname,
            TopInetnum,
            TopOrgname,
            TopDateAdded,
            TopLastCheck,
            TopLastHit,
            TopHits
        },
        props: [
            'lists',
            'lists6'
        ],
        data() {
            return {
                showList: null,
                showTop: null,
                topList: [
                    this.__('Show by ip1'),
                    this.__('Show by country'),
                    this.__('Show by hits'),
                    this.__('Show by netname'),
                    this.__('Show by inetnum'),
                    this.__('Show by orgname'),
                    this.__('Show by date added'),
                    this.__('Show by last hit'),
                    this.__('Show by last check'),
                ]
            }
        },
        methods: {
            changeList(value) {
                this.showList = value;
            },
            changeTop(value) {
                this.showTop = value;
            }
        }
    }
</script>
