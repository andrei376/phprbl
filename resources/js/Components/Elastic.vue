<template>
    <div class="position-relative">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-dark">
                <tr>
                    <th colspan="3" class="w-full text-center">{{ this.rangeInfo }} {{ this.__('[Searching]') }} {{ this.regexInfo }}</th>
                </tr>
                <tr>
                    <th class="w-2/12">{{ this.__('[Date]') }}</th>
                    <th class="w-2/12">{{ this.__('[Host]') }}</th>
                    <th class="w-8/12">{{ this.__('[Message]') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="tableData.length <= 0">
                    <td colspan="3" class="text-center">{{ this.__('[No data]') }}</td>
                </tr>
                <tr v-for="row in tableData">
                    <td>
                        {{ row.time_format }}
                        <br>
                        ({{ row.time_ago }})
                    </td>
                    <td>
                        {{ row.sys }}
                    </td>
                    <td>
<!--                        <div style="overflow: auto; overflow-x: scroll;max-width: 1300px;">
                            <pre>{{ row }}</pre>
                        </div>
                        <samp>
                            {{ row.msg }}
                        </samp>
                        <br>-->
                        <samp v-html="row.msg2">
                        </samp>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
        <nav v-if="pagination && tableData.length > 0" class="d-flex justify-content-between">
            <span class="d-flex" style="margin-top: 8px;">
                <i>Displaying {{ pagination.meta.from }} - {{ pagination.meta.to }} of {{ pagination.meta.total }} entries.</i>
            </span>
            <ul class="pagination justify-content-end">
                <li class="page-item" :class="{'disabled' : currentPage === 1}">
                    <a class="page-link" href="#" @click.prevent="$event.target.blur();changePage(currentPage - 1)">Previous</a>
                </li>
                <li v-for="page in pagesNumber" class="page-item"
                    :class="{'active': page === pagination.meta.current_page}">
                    <a href="javascript:void(0)" @click.prevent="$event.target.blur();changePage(page)" class="page-link">{{ page }}</a>
                </li>
                <li class="page-item" :class="{'disabled': currentPage === pagination.meta.last_page }">
                    <a class="page-link" href="#" @click.prevent="$event.target.blur();changePage(currentPage + 1)">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: "Elastic",

    props: [
        'iplong',
        'mask'
    ],
    data() {
        return {
            isLoading: false,
            rangeInfo: null,
            regexInfo: null,
            tableData: [],
            pagination: {
                meta: { to: 1, from: 1}
            },
            currentPage: 1,
            offset: 4,
            perPage: this.perPageRows ? this.perPageRows : 10,
            sortedColumn: this.orderBy,
            order: this.orderDir ? this.orderDir : 'desc'
        }
    },
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
    methods: {
        getElastic: function() {
            this.isLoading = true;

            let postData = {
                page: this.currentPage,
                perPage: this.perPage,
                column: this.sortedColumn,
                order: this.order,
                search: this.field,

                iplong: this.iplong,
                mask: this.mask
            };

            axios.get(this.route('elastic.rbl', postData)).then(function(response){

                this.rangeInfo = response.data.rangeInfo;
                this.regexInfo = response.data.regexInfo;

                this.pagination = response.data;
                this.tableData = response.data.data;

                this.isLoading = false;
            }.bind(this)).catch(errors => {
                if (errors.response.status == 401 && errors.response.statusText == 'Unauthorized') {
                    window.location = this.route('login');
                }
                this.$noty.error(this.__('Error fetching information.'), {
                    modal: true
                });
                this.tableData = []

                this.isLoading = false;
            });
        },
        changePage(pageNumber) {
            this.currentPage = pageNumber;
            this.getElastic();
        },
    },
    mounted() {
        this.getElastic();
    }
}
</script>
