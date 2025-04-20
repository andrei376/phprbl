<template>
    <breeze-authenticated-layout>
        <div class="pt-2 pb-12">
            <div class="col-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="update" novalidate autocomplete="off">
                        <table class="table table-striped table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th colspan="8" class="text-center">{{ this.__('List') }} {{ list }} ({{ otherIp.length }} {{ this.__("doubled IP") }}?)</th>
                                </tr>
                                <tr>
                                    <th class="w-2/12">{{ this.__('IP') }}</th>
                                    <th class="w-2/12">{{ this.__('inetnum') }}</th>
                                    <th class="w-2/12">{{ this.__('netname') }}</th>
                                    <th class="w-2/12">{{ this.__('orgname') }}</th>
                                    <th class="w-1/12">{{ this.__('country') }}</th>
                                    <th class="w-1/12">
                                        {{ this.__('delete') }}
                                        <br><br>
                                        <a class="underline" @click="$event.target.blur(); forceChecked('delete', true)">S</a>&nbsp;&nbsp;/&nbsp;
                                        <a class="underline" @click="$event.target.blur(); forceChecked('delete', false)">D</a>&nbsp;&nbsp;/&nbsp;
                                        <a class="underline" @click="$event.target.blur(); toggleChecked('delete')">I</a>
                                    </th>
                                    <th class="w-1/12">
                                        {{ this.__('active') }}
                                        <br><br>
                                        <a class="underline" @click="$event.target.blur(); forceChecked('active', true)">S</a>&nbsp;&nbsp;/&nbsp;
                                        <a class="underline" @click="$event.target.blur(); forceChecked('active', false)">D</a>&nbsp;&nbsp;/&nbsp;
                                        <a class="underline" @click="$event.target.blur(); toggleChecked('active')">I</a>
                                    </th>
                                    <th class="w-1/12">
                                        {{ this.__('checked') }}
                                        <br><br>
                                        <a class="underline" @click="$event.target.blur(); forceChecked('checked', true)">S</a>&nbsp;&nbsp;/&nbsp;
                                        <a class="underline" @click="$event.target.blur(); forceChecked('checked', false)">D</a>&nbsp;&nbsp;/&nbsp;
                                        <a class="underline" @click="$event.target.blur(); toggleChecked('checked')">I</a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in otherIp">
                                    <td>
                                        <input type="hidden" :name="'data['+ row.id +'][id]'" v-model="form.fdata[row.id].id">
                                        <span>{{ row.long2ip }}/{{ row.mask }}</span>
                                        <span v-if="id === row.id" class="badge bg-success mr-1 ml-3" style="min-width: 2rem;">{{ this.__('self') }}</span>
                                        <br>
                                        <span class="">{{ row.range }}</span>
                                    </td>
                                    <td>
                                        {{ row.inetnum }}
                                    </td>
                                    <td>{{ row.netname }}</td>
                                    <td>{{ row.orgname }}</td>
                                    <td>{{ row.country }}</td>
                                    <td>
                                        <input type="checkbox" :id="'delete'+row.id" :name="'data['+ row.id +'][delete]'" v-model="form.fdata[row.id].delete" >
                                    </td>
                                    <td>
                                        <input type="checkbox" :id="'active'+row.id" :name="'data['+ row.id +'][active]'" v-model="form.fdata[row.id].active" >
                                    </td>
                                    <td>
                                        <input type="checkbox" :id="'checked'+row.id" :name="'data['+ row.id +'][checked]'" v-model="form.fdata[row.id].checked" >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
                        <div class="d-flex justify-content-between">
                            <inertia-link v-if="ipv6" class="btn btn-primary" :href="this.route('rbl.show6', {'id': this.id, 'list': this.list})">{{ this.__('Back') }}</inertia-link>
                            <inertia-link v-else class="btn btn-primary" :href="this.route('rbl.show4', {'id': this.id, 'list': this.list})">{{ this.__('Back') }}</inertia-link>

                            <button type="submit" @click="$event.target.blur();" :class="{ 'opacity-25': isLoading }" :disabled="isLoading" class="btn btn-success">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ this.__('Save')}}
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'

export default {
    name: "Many",
    components: {
        BreezeAuthenticatedLayout
    },

    props: [
        'id',
        'list',
        'cidr',
        'ipInfo',
        'otherIp',
        'formData',
        'ipv6'
    ],

    data() {
        return {
            form: this.$inertia.form({
                fdata: this.formData,
            }),
            isLoading: false
        }
    },

    methods: {
        forceChecked(name, status) {
            $("input[id^="+name+"]").each(function(){
                $(this).prop("checked", status);

                const event = new Event('change');
                this.dispatchEvent(event);
            });
        },
        toggleChecked(name) {
            $("input[id^="+name+"]").each(function(){
                $(this).prop("checked", function(i, val) {
                    return !val;
                });

                const event = new Event('change');
                this.dispatchEvent(event);
            });
        },

        update() {
            this.isLoading = true;
            let url = this.route('update.many4', {
                'id': this.id,
                'list': this.list,
                'cidr': this.cidr
            });

            this.form.post(url, {
                onSuccess: () => {

                    this.$noty.success("Information saved.");

                    eventBus.emit('refreshRblStats');
                    this.isLoading = false;
                },
                onError: () => {
                    this.$noty.error('Error saving information', {
                        modal: true
                    });
                    this.isLoading = false;
                }
            });
        }
    }
}
</script>
