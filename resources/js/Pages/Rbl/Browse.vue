<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        2
                        <inertia-link v-for="list in this.lists" :href="this.route('rbl.browse', { 'list': list})" @click="$event.target.blur();" type="button" class="btn mr-5"  :class="list === showList ? 'btn-primary' : 'btn-outline-secondary'">
                            {{ list }}
                        </inertia-link>
                        <inertia-link v-for="list in this.lists6" :href="this.route('rbl.browse', { 'list': list})" @click="$event.target.blur();" type="button" class="btn mr-5"  :class="list === showList ? 'btn-primary' : 'btn-outline-secondary'">
                            {{ list }}
                        </inertia-link>

                        <table class="table table-sm table-bordered mt-5">
                            <thead class="table-dark">
                                <tr>
                                    <th colspan="10" class="text-center">{{ this.__('List')}} {{ showList }}</th>
                                </tr>
                                <tr>
                                    <th class="w-16" @click="sortByColumn('id')">
                                        {{ this.__('ID') }}

                                        <span v-if="'id' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>

                                    <th class="" @click="sortByColumn('iplong')">
                                        {{ this.__('IP') }}

                                        <span v-if="'iplong' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="" @click="sortByColumn('mask')">
                                        {{ this.__('mask')}}

                                        <span v-if="'mask' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-2/12" @click="sortByColumn('inetnum')">
                                        {{ this.__('inetnum') }}

                                        <span v-if="'inetnum' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-2/12" @click="sortByColumn('netname')">
                                        {{ this.__('netname') }}

                                        <span v-if="'netname' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-2/12" @click="sortByColumn('orgname')">
                                        {{ this.__('orgname') }}

                                        <span v-if="'orgname' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-1/12" @click="sortByColumn('country')">
                                        {{ this.__('country') }}

                                        <span v-if="'country' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-1/12" @click="sortByColumn('date_added')">
                                        {{ this.__('date_added') }}

                                        <span v-if="'date_added' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-1/12" @click="sortByColumn('last_check')">
                                        {{ this.__('last_check') }}

                                        <span v-if="'last_check' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                    <th class="w-24" @click="sortByColumn('hits_sum_count')">
                                        {{ this.__('Total hits') }}

                                        <span v-if="'hits_sum_count' === sortedColumn">
                                        <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                        <i v-else class="bi bi-caret-down"></i>
                                        </span>
                                        <span v-else>
                                        <i class="bi bi-chevron-bar-expand float-right"></i>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="table-hover">
                                <tr>
                                    <td>
                                        <div class="w-16">
                                            <input type="text" class="form-control form-control-sm w-16" name="field[id]" v-model="field.id">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[ip]" v-model="field.ip">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[mask]" v-model="field.mask">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[inetnum]" v-model="field.inetnum">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[netname]" v-model="field.netname">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[orgname]" v-model="field.orgname">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[country]" v-model="field.country">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[date_added_format]" v-model="field.date_added">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-row">
                                            <div class="col-12">
                                                <input type="text" class="form-control form-control-sm" name="field[last_check]" v-model="field.last_check">
                                            </div>
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr
                                    v-for="(row, index) in tableData"
                                    v-bind:style="[(index % 2) === 0 && !isHover[index] ? 'background-color: rgba(0, 0, 0, 0.05);' : '']"
                                    @mouseenter="isHover[index]=true;"
                                    @mouseleave="isHover[index]=false;"
                                    :class="{ 'bg-blue-100' : isHover[index] }"
                                >
                                    <td>{{ row.id }}</td>
                                    <td>
                                        <inertia-link v-if="ipv6" :href="this.route('rbl.show6', {'id': row.id, 'list': showList})">{{ row.long2ip }}/{{ row.mask }}</inertia-link>
                                        <inertia-link v-else :href="this.route('rbl.show4', {'id': row.id, 'list': showList})">{{ row.long2ip }}/{{ row.mask }}</inertia-link>
                                    </td>
                                    <td>{{ row.mask }}</td>
                                    <td>{{ row.inetnum }}</td>
                                    <td>{{ row.netname }}</td>
                                    <td>{{ row.orgname }}</td>
                                    <td>{{ row.country }}</td>
                                    <td>
                                        {{ row.date_added_format }}
                                        <br>
                                        {{ row.date_added_ago }}
                                    </td>
                                    <td>
                                        {{ row.last_check_format }}
                                        <br>
                                        {{ row.last_check_ago }}
                                    </td>
                                    <td>
                                        {{ row.hits_sum_count_format }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
                        <nav v-if="pagination && tableData.length > 0" class="d-flex justify-content-between">
                            <span class="d-flex" style="margin-top: 8px;"><i>Displaying {{ pagination.data.length }} of {{ pagination.meta.total }} entries.</i></span>
                            <ul class="pagination justify-content-end">
                                <li class="page-item" :class="{'disabled' : currentPage === 1}">
                                    <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
                                </li>
                                <li v-for="page in pagesNumber" class="page-item"
                                    :class="{'active': page === pagination.meta.current_page}">
                                    <a href="javascript:void(0)" @click.prevent="changePage(page)" class="page-link">{{ page }}</a>
                                </li>
                                <li class="page-item" :class="{'disabled': currentPage === pagination.meta.last_page }">
                                    <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'
import Input from "@/Components/Input";

export default {
    components: {
        Input,
        BreezeAuthenticatedLayout,
    },
    created() {
        this.getBrowse();
    },
    props: [
        'showList',
        'lists',
        'lists6',
        'ipv6'
    ],

    computed: {
        pagesNumber() {
            if (!this.pagination.meta.to) {
                return [];
            }

            let from = this.pagination.meta.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }

            let to = from + (this.offset * 2);
            if (to >= this.pagination.meta.last_page) {
                to = this.pagination.meta.last_page;
            }
            let pagesArray = [];
            for (let page = from; page <= to; page++) {
                pagesArray.push(page);
            }
            return pagesArray;
        },
        totalData() {
            return (this.pagination.meta.to - this.pagination.meta.from) + 1;
        }
    },

    data() {
        return {
            field: {
                id: '',
                ip: '',
                inetnum: '',
                netname: '',
                orgname: '',
                country: '',
                date_added: '',
                last_check: ''
            },
            isLoading: false,
            isHover: [],
            tableData: [],
            pagination: {
                meta: { to: 1, from: 1}
            },
            currentPage: 1,
            offset: 4,
            perPage: 5,
            sortedColumn: 'hits_sum_count',
            order: 'desc'
        }
    },

    methods: {
        getBrowse() {
            this.isLoading = true;
            let dataFetchUrl = this.route('rbl.getBrowse', {
                list: this.$props.showList
            });

            let postData = {
                page: this.currentPage,
                perPage: this.perPage,
                column: this.sortedColumn,
                order: this.order,
                search: this.field
            };

            axios.post(dataFetchUrl, postData).then(function(response){
                this.pagination = response.data;
                this.tableData = response.data.data;
                this.isLoading = false;
            }.bind(this)).catch(() => {
                this.tableData = []
                this.isLoading = false;
            });
        },
        changePage(pageNumber) {
            this.currentPage = pageNumber
            this.getBrowse()
        },
        sortByColumn(column) {
            if (column === this.sortedColumn) {
                this.order = (this.order === 'asc') ? 'desc' : 'asc'
            } else {
                this.sortedColumn = column
                this.order = 'asc'
            }
            this.getBrowse();
        },
    },
    watch: {
        field: {
            handler: _.debounce(function() {
                this.getBrowse();
            }, 350),
            deep: true
        }
    }
}
</script>
