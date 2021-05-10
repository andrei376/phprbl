<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="w-8/12 mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <table class="table table-striped table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th class="w-1/12">{{ this.__('[ID]') }}</th>
                                <th class="w-5/12">{{ this.__('[Name]') }}</th>
                                <th class="w-2/12">{{ this.__('[currentsn]') }}</th>
                                <th class="w-2/12">{{ this.__('[lastsn]') }}</th>
                                <th class="w-2/12">{{ this.__('[Actions]') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="lists.length <= 0">
                                <td colspan="5" class="border p-1 text-center">{{ this.__('[No data]') }}</td>
                            </tr>
                            <tr v-for="list in lists">
                                <td class="p-1">{{ list.id }}</td>
                                <td class="p-1">{{ list.name }}</td>
                                <td class="p-1">{{ list.currentsn }}</td>
                                <td class="p-1">{{ list.lastsn }}</td>
                                <td class="p-1">
                                    <div class="btn-group btn-group-sm w-full" role="group" aria-label="{{ this.__('[Actions]') }}">
                                        <inertia-link class="btn btn-primary" :href="this.route('setup.edit', list)">{{ this.__('[Edit]') }}</inertia-link>
                                        <button @click="deleteRow(list)" type="button" class="btn btn-danger">{{ this.__('[Delete]') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                        {{ lists.length }} {{ this.__('[entries]') }}

                        <div class="flex items-center justify-end mt-4">
                            <inertia-link class="btn btn-primary" :href="this.route('setup.create')">{{ this.__('[New list]') }}</inertia-link>
                        </div>


                        <div class="mt-5">
                            <table class="table table-striped table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ this.__('[ID]') }}</th>
                                        <th>{{ this.__('[Variable name]') }}</th>
                                        <th>{{ this.__('[Value]') }}</th>
                                        <th>{{ this.__('[Actions]') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="variables.length <= 0">
                                        <td colspan="4" class="border p-1 text-center">{{ this.__('[No data]') }}</td>
                                    </tr>
                                    <tr v-for="row in variables">
                                        <td class="p-1">{{ row.id }}</td>
                                        <td class="p-1">{{ row.name }}</td>
                                        <td class="p-1">{{ row.value }}</td>
                                        <td class="p-1">
                                            <div class="btn-group btn-group-sm w-full" role="group" aria-label="{{ this.__('[Actions]') }}">
                                                <inertia-link class="btn btn-primary" :href="this.route('setup.editvar', row)">{{ this.__('[Edit]') }}</inertia-link>

                                                <button @click="deleteVarRow(row)" type="button" class="btn btn-danger">{{ this.__('[Delete]') }}</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{ variables.length }} {{ this.__('[entries]') }}

                            <div class="flex items-center justify-end mt-4">
                                <inertia-link class="btn btn-primary" :href="this.route('setup.createvar')">{{ this.__('[New variable]') }}</inertia-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated';
    import BreezeButton from '@/Components/Button';

    export default {
        components: {
            BreezeAuthenticatedLayout,
            BreezeButton
        },

        props: [
            'lists',
            'variables'
        ],

        methods: {
            deleteRow: function (data) {

                if (!confirm(this.__('Are you sure you want to delete this list?'))) return;

                this.$inertia.delete(this.route("setup.destroy", "") + '/' + data.id).catch(() => {
                    this.$noty.error(this.__('[Error deleting list.]'));
                });
            },
            deleteVarRow: function (data) {

                if (!confirm(this.__('Are you sure you want to delete this variable?'))) return;

                this.$inertia.delete(this.route("setup.destroyvar", "") + '/' + data.id).catch(() => {
                    this.$noty.error(this.__('[Error deleting variable.]'), {
                        modal: true
                    });
                });
            }
        }
    }
</script>
