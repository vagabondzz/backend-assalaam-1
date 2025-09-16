@include('include.htmlstart')
@include('include.sidebarstart')
<div class=" w-full sm:ml-64">
    <div class="w-full mt-16 overflow-x-hidden border-t flex flex-col">
        <main class="w-full flex-grow p-6 sm:p-4 h-screen">
            {{-- coba --}}
            <div class=" grid grid-cols-1 sm:grid-cols-3  mt-2 mb-4 bg-gray-100/0 dark:bg-gray-800/0">
                @include('include.sayhi')
                <div
                    class=" dark:bg-gray-700 sm:mr-2 rounded-xl p-4 my-2 shadow-md shadow-gray-900  relative overflow-hidden ">
                    <div class="w-24 h-24 bg-blue-400 dark:bg-blue-600 rounded-full absolute -right-[5px] -top-[40px]">
                    </div>
                    <div
                        class="w-24 h-24 bg-blue-300/50 dark:bg-blue-400/50 rounded-full absolute -right-[30px] -top-[15px]">
                    </div>
                    <div class="relative " x-data="welcomeCard()" x-init="init()">
                        @include('include.tanggal')
                        <div class="" x-data="tanggalSekarang()" x-init="init()">
                            <p x-text="tanggal" class="bg-blue-500 w-max p-2 rounded-xl text-white font-semibold">
                            </p>
                        </div>
                        <div class=" flex flex-row text-gray-700 dark:text-gray-200 mt-2 ml-1">
                            <span class="text-md font-bold" x-text="`${greeting}`">
                            </span>
                            <p class="text-md font-bold -ml-2"> &nbsp {{ Auth::user()->name }}!</p>
                        </div>
                        <p class="text-gray-700 dark:text-gray-200 font-medium block ml-1">Semoga harimu menyenangkan!
                            &#129303; </p>
                    </div>
                </div>
                <div class=" col-span-2 my-2 grid sm:grid-cols-3 gap-2">
                    {{-- card 1 --}}
                    <div
                        class="relative shadow-md shadow-gray-950 flex flex-col bg-clip-border rounded-xl dark:bg-gray-700 from text-gray-700 overflow-hidden">
                        <div
                            class="w-24 h-24 bg-lime-300 dark:bg-lime-600 rounded-full absolute -right-[5px] -top-[40px]">
                        </div>
                        <div
                            class="w-24 h-24 bg-lime-200/60 dark:bg-lime-400/30 rounded-full absolute -right-[30px] -top-[15px]">
                        </div>
                        <div class="p-4">
                            <p
                                class="block antialiased relative font-roboto text-sm leading-normal font-bold text-gray-700 dark:text-gray-200">
                                MEMBER AKTIF
                            </p>
                            <h4
                                class="block antialiased tracking-normal relative font-sans text-3xl font-semibold leading-snug text-gray-700 dark:text-gray-200">
                                {{ $members_data['active_member'] }}
                            </h4>
                        </div>
                        {{-- Progress bar --}}
                        <div class="p-4" x-data="{
                            activeMembers: {{ $members_data['active_member'] }},
                            totalMembers: {{ $members_data['total_member'] }},
                            width: 0,
                            calculateWidth() {
                                const active = Number(this.activeMembers);
                                const total = Number(this.totalMembers);
                        
                                if (total > 0) {
                                    this.width = Math.round((active / total) * 100);
                                }
                                console.log('Active Members:', active, 'Total Members:', total, 'Calculated Width:', this.width);
                            }
                        }" x-init="calculateWidth()">
                            <p class="text-sm font-semibold -mt-8 text-gray-700 dark:text-gray-200 ">
                                Persentase Aktif:
                            </p>
                            <div class="bg-gray-400 h-6" role="progressbar" :aria-valuenow="width" aria-valuemin="0"
                                aria-valuemax="100">
                                <div class="bg-lime-400 h-6 text-center text-gray-800 text-sm font-bold transition-all duration-500"
                                    :style="`width: ${width}%; transition: width 2s;`" x-text="`${width}%`">
                                </div>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                    </div>

                    {{-- card 2 --}}
                    <div
                        class="relative shadow-md shadow-gray-950 flex flex-col bg-clip-border rounded-xl dark:bg-gray-700 from text-gray-700 overflow-hidden">
                        <div
                            class="w-24 h-24 bg-red-300 dark:bg-rose-600 rounded-full absolute -right-[5px] -top-[40px]">
                        </div>
                        <div
                            class="w-24 h-24 bg-red-200/50 dark:bg-rose-400/50 rounded-full absolute -right-[30px] -top-[15px]">
                        </div>
                        <div class="p-4">
                            <p
                                class="block antialiased relative font-roboto text-sm leading-normal font-bold text-gray-700 dark:text-gray-200">
                                MEMBER TIDAK AKTIF
                            </p>
                            <h4
                                class="block antialiased tracking-normal relative font-sans text-3xl font-semibold leading-snug text-gray-700 dark:text-gray-200">
                                {{ $members_data['inactive_member'] }}
                            </h4>
                        </div>
                        {{-- Progress bar --}}
                        <div class="p-4" x-data="{
                            activeMembers: {{ $members_data['inactive_member'] }},
                            totalMembers: {{ $members_data['total_member'] }},
                            width: 0,
                            calculateWidth() {
                                const active = Number(this.activeMembers);
                                const total = Number(this.totalMembers);
                        
                                if (total > 0) {
                                    this.width = Math.round((active / total) * 100);
                                }
                                console.log('Active Members:', active, 'Total Members:', total, 'Calculated Width:', this.width);
                            }
                        }" x-init="calculateWidth()">
                            <p class="text-sm font-semibold -mt-8 text-gray-700 dark:text-gray-200 ">
                                Persentase Tidak Aktif:
                            </p>
                            <div class="bg-gray-400 h-6  mt-1" role="progressbar" :aria-valuenow="width"
                                aria-valuemin="0" aria-valuemax="100">
                                <div class="bg-red-600 h-6  text-center text-gray-800 text-sm font-bold transition-all duration-500"
                                    :style="`width: ${width}%; transition: width 2s;`" x-text="`${width}%`">
                                </div>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                    </div>

                    {{-- card 3 --}}
                    <div
                        class="relative shadow-md shadow-gray-900 flex flex-col bg-clip-border rounded-xl  from text-gray-700 dark:bg-gray-700 overflow-hidden">

                        <div
                            class="w-24 h-24 bg-orange-300 dark:bg-orange-600 rounded-full absolute -right-[5px] -top-[40px]">
                        </div>
                        <div
                            class="w-24 h-24 bg-orange-200/40 dark:bg-orange-400/40 rounded-full absolute -right-[30px] -top-[15px]">
                        </div>
                        <div class="p-4">
                            <p
                                class="block antialiased relative dark:text-gray-200 font-roboto text-sm leading-normal font-bold text-gray-700">
                                TOTAL MEMBER
                            </p>
                            <h4
                                class="block antialiased tracking-normal dark:text-gray-200 relative font-sans text-3xl font-semibold leading-snug text-gray-700">
                                {{ $members_data['total_member'] }}
                            </h4>
                        </div>
                        <div
                            class="mx-4 mb-2 relative -right-[255px] sm:-right-[135px] rounded-xl overflow-hidden bg-orange-400/50 text-orange-400 dark:text-gray-200 grid h-12 w-12 place-items-center">
                            <ion-icon class="w-8 h-8" name="people-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
            {{-- chart --}}
            <div class="mb-6 card  dark:bg-gray-900 dark:text-gray-200 shadow-md">
                <div class="card-title p-4">Jumlah Anggota Per Bulan</div>
                {!! $chart->container() !!}
                {!! $chart->script() !!}
            </div>
            {{-- chart --}}
        </main>
    </div>
</div>
@include('include.htmlend')