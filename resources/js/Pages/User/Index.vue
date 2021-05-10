<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="w-8/12 mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- <pre>{{ users }}</pre> -->
                        <table class="table table-striped table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th class="w-1/12 border">{{ this.__('ID') }}</th>
                                <th class="w-9/12 border">{{ this.__('Email') }}</th>
                                <th class="w-2/12 border">{{ this.__('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users">
                                <td class="border p-1">{{ user.id }}</td>
                                <td class="border p-1" :title="user.VerifiedEmailText">
                                    <span :class="user.VerifiedEmailClass">
                                    {{ user.email }}
                                    </span>
                                </td>
                                <td class="border p-1">
                                    <div class="btn-group btn-group-sm w-full" role="group" aria-label="{{ this.__('Actions') }}">
                                        <inertia-link class="btn btn-primary" :href="this.route('users.edit', user.id)">{{ this.__('Edit') }}</inertia-link>

                                        <button @click="deleteRow(user)" type="button" class="btn btn-danger">{{ this.__('Delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                        {{ users.length }} entries
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'

    export default {
        components: {
            BreezeAuthenticatedLayout,
        },

        props: [
            'users',
            'errors'
        ],
        methods: {
            deleteRow: function (data) {

                if (!confirm('Are you sure you want to delete this user?')) return;

                this.$inertia.delete(this.route("users.destroy", "") + '/' + data.id).catch(() => {
                    alert('Error deleting user');
                });
            }
        }
    }
</script>
