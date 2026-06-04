<div x-data="filesApp()" class="max-w-6xl mx-auto p-4 page-enter">
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden mb-6">
        <div class="p-4 sm:p-6 bg-[#795548] text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold Lao-font">ຈັດການປຶ້ມ</h1>
                    <p class="text-white/70 text-sm mt-1">ອັບໂຫຼດ ແກ້ໄຂ ລຶບ ຂໍ້ມູນປຶ້ມ</p>
                </div>
                <a href="<?= url('/search-books') ?>" class="p-2 rounded-full bg-white/10 hover:bg-white/20 text-white/70 hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>
        </div>
    </div>
 
    <!-- Book List -->
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-white/20 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800 Lao-font">
                ລາຍການປຶ້ມທັງໝົດ (<span x-text="books.length"></span>)
            </h2>
            <button @click="openUploadModal()"
                    class="px-4 py-2 rounded-xl bg-[#795548] text-white font-bold text-sm hover:bg-[#5E412D] transition-colors Lao-font whitespace-nowrap">
                + ອັບໂຫຼດປຶ້ມໃໝ່
            </button>
        </div>

        <div x-show="!books.length" class="p-8 text-center text-gray-400 Lao-font">
            ຍັງບໍ່ມີປຶ້ມ
        </div>

        <div class="max-h-[500px] overflow-y-auto">
        <template x-for="(book, i) in books" :key="book.slug">
            <div class="p-4 sm:p-6 border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                <div class="flex items-start gap-4">
                    <!-- Cover -->
                    <div class="flex-shrink-0 w-16 h-20 sm:w-20 sm:h-24 rounded-lg overflow-hidden bg-gray-100 shadow-sm">
                        <img :src="coverUrl(book.slug) || '<?= url('assets/images/book-placeholder.svg') ?>'"
                             :alt="book.title"
                             class="w-full h-full object-cover"
                             loading="lazy"
                             @error="$event.target.src='<?= url('assets/images/book-placeholder.svg') ?>'; $event.target.style.objectFit='contain'; $event.target.style.padding='4px'; $event.target.style.backgroundColor='#6B553A'; $event.target.style.opacity='0.6'">
                    </div>

                    <!-- Book Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-800 Lao-font text-sm sm:text-base truncate" x-text="book.title || book.slug"></h3>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                          :class="book.type === 'pdf' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'"
                                          x-text="book.type"></span>
                                    <span x-text="book.totalPages + ' ໜ້າ'"></span>
                                    <span x-show="book.year" x-text="' | ' + book.year"></span>
                                </p>
                                <p class="text-xs text-gray-300 font-mono mt-0.5 truncate" x-text="book.slug"></p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <a :href="'<?= url('/search-books') ?>' + '/' + book.slug + '/page/1'"
                                   target="_blank"
                                   class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-[#795548] transition-colors"
                                   title="ເບິ່ງ">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button @click="editBook(i)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-600 transition-colors" title="ແກ້ໄຂ">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="uploadCover(i)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-green-600 transition-colors" title="ອັບໂຫຼດຮູບປົກ">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </button>
                                <button @click="deleteBook(book.slug, book.title || book.slug)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-red-600 transition-colors" title="ລຶບ">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        </div>
    </div>

    <!-- Upload Modal -->
    <div x-show="uploadModal.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="uploadModal.show = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 my-auto"
             @click.away="uploadModal.show = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800 Lao-font">ອັບໂຫຼດປຶ້ມໃໝ່</h3>
                <button @click="uploadModal.show = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="uploadBook()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">ຊື່ປຶ້ມ</label>
                        <input type="text" x-model="form.title" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-sm Lao-font">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">ປີ (option)</label>
                        <input type="number" x-model="form.year" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">Slug (option - ຖ້າຫວ່າງຈະສ້າງອັດຕະໂນມັດ)</label>
                    <input type="text" x-model="form.slug" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-sm font-mono">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">
                        ໄຟລ໌ (.pdf, .docx, .doc)
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" @change="form.file = $event.target.files[0]" accept=".pdf,.docx,.doc"
                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#795548] file:text-white file:text-sm file:font-bold file:cursor-pointer hover:file:bg-[#5E412D]">
                    </div>
                    <p class="text-xs text-gray-400 mt-1 Lao-font">* ໄຟລ໌ຈະຖືກລຶບອັດຕະໂນມັດຫຼັງຈາກແປງສຳເລັດ</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">
                        ຮູບປົກ (option - .png, .jpg, .jpeg)
                    </label>
                    <div class="relative">
                        <input type="file" @change="form.cover = $event.target.files[0]" accept=".png,.jpg,.jpeg"
                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] outline-none text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#795548] file:text-white file:text-sm file:font-bold file:cursor-pointer hover:file:bg-[#5E412D]">
                    </div>
                </div>

                <div x-show="form.uploadProgress > 0" class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-[#795548] h-2 rounded-full transition-all duration-300" :style="'width: ' + form.uploadProgress + '%'"></div>
                </div>

                <div x-show="form.error" class="text-red-500 text-sm Lao-font" x-text="form.error"></div>
                <div x-show="form.success" class="text-green-600 text-sm Lao-font" x-text="form.success"></div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="uploadModal.show = false"
                            class="flex-1 px-4 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-colors Lao-font">ຍົກເລີກ</button>
                    <button type="submit" :disabled="form.uploading"
                            class="flex-1 px-4 py-2 rounded-xl bg-[#795548] text-white font-bold hover:bg-[#5E412D] transition-colors disabled:opacity-50 disabled:cursor-not-allowed Lao-font"
                            x-text="form.uploading ? 'ກຳລັງອັບໂຫຼດ...' : 'ອັບໂຫຼດ'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModal.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="editModal.show = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 my-auto"
             @click.away="editModal.show = false">
            <h3 class="text-lg font-bold text-gray-800 Lao-font mb-4">ແກ້ໄຂປຶ້ມ</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">ຊື່</label>
                    <input type="text" x-model="editModal.title" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-sm Lao-font">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 Lao-font mb-1">ປີ</label>
                    <input type="number" x-model="editModal.year" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] focus:ring-2 focus:ring-[#795548]/20 outline-none text-sm">
                </div>
            </div>

            <div x-show="editModal.error" class="mt-3 text-red-500 text-sm Lao-font" x-text="editModal.error"></div>
            <div x-show="editModal.success" class="mt-3 text-green-600 text-sm Lao-font" x-text="editModal.success"></div>

            <div class="flex items-center gap-2 mt-6">
                <button @click="editModal.show = false" class="flex-1 px-4 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-colors Lao-font">ຍົກເລີກ</button>
                <button @click="saveEdit()" :disabled="editModal.saving"
                        class="flex-1 px-4 py-2 rounded-xl bg-[#795548] text-white font-bold hover:bg-[#5E412D] transition-colors disabled:opacity-50 Lao-font"
                        x-text="editModal.saving ? 'ກຳລັງບັນທຶກ...' : 'ບັນທຶກ'">
                </button>
            </div>
        </div>
    </div>

    <div x-show="coverModal.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="coverModal.show = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 my-auto"
             @click.away="coverModal.show = false">
            <h3 class="text-lg font-bold text-gray-800 Lao-font mb-4">ອັບໂຫຼດຮູບປົກ</h3>

            <p class="text-sm text-gray-500 Lao-font mb-3" x-text="'ສຳລັບ: ' + (coverModal.title || coverModal.slug)"></p>

            <input type="file" @change="coverModal.file = $event.target.files[0]" accept=".png,.jpg,.jpeg"
                   class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#795548] outline-none text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#795548] file:text-white file:text-sm file:font-bold file:cursor-pointer hover:file:bg-[#5E412D]">

            <div x-show="coverModal.error" class="mt-3 text-red-500 text-sm Lao-font" x-text="coverModal.error"></div>
            <div x-show="coverModal.success" class="mt-3 text-green-600 text-sm Lao-font" x-text="coverModal.success"></div>

            <div class="flex items-center gap-2 mt-6">
                <button @click="coverModal.show = false" class="flex-1 px-4 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-colors Lao-font">ຍົກເລີກ</button>
                <button @click="saveCover()" :disabled="coverModal.uploading"
                        class="flex-1 px-4 py-2 rounded-xl bg-[#795548] text-white font-bold hover:bg-[#5E412D] transition-colors disabled:opacity-50 Lao-font"
                        x-text="coverModal.uploading ? 'ກຳລັງອັບໂຫຼດ...' : 'ອັບໂຫຼດ'">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function filesApp() {
    return {
        init() {
            this.$watch('editModal.show', val => {
                document.body.style.overflow = val ? 'hidden' : '';
            });
            this.$watch('coverModal.show', val => {
                document.body.style.overflow = val ? 'hidden' : '';
            });
            this.$watch('uploadModal.show', val => {
                document.body.style.overflow = val ? 'hidden' : '';
            });
        },
        books: JSON.parse(document.getElementById('files-books-data')?.textContent || '[]'),
        form: {
            title: '',
            slug: '',
            year: new Date().getFullYear(),
            file: null,
            cover: null,
            uploading: false,
            uploadProgress: 0,
            error: '',
            success: '',
        },
        editModal: {
            show: false,
            index: -1,
            slug: '',
            title: '',
            year: '',
            saving: false,
            error: '',
            success: '',
        },
        coverModal: {
            show: false,
            index: -1,
            slug: '',
            title: '',
            file: null,
            uploading: false,
            error: '',
            success: '',
        },
        uploadModal: {
            show: false,
        },

        coverUrl(slug) {
            const book = this.books.find(b => b.slug === slug);
            return book?.coverUrl || '';
        },

        openUploadModal() {
            this.form.title = '';
            this.form.slug = '';
            this.form.year = new Date().getFullYear();
            this.form.file = null;
            this.form.cover = null;
            this.form.uploadProgress = 0;
            this.form.error = '';
            this.form.success = '';
            this.uploadModal.show = true;
        },

        async uploadBook() {
            if (!this.form.file) {
                this.form.error = 'ກະລຸນາເລືອກໄຟລ໌';
                return;
            }
            this.form.error = '';
            this.form.success = '';
            this.form.uploading = true;
            this.form.uploadProgress = 30;

            const fd = new FormData();
            fd.append('file', this.form.file);
            fd.append('title', this.form.title);
            fd.append('slug', this.form.slug);
            fd.append('year', this.form.year);
            if (this.form.cover) fd.append('cover', this.form.cover);

            try {
                const res = await fetch('<?= url('/upload/store') ?>', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    this.form.success = 'ອັບໂຫຼດສຳເລັດ';
                    this.form.uploadProgress = 100;
                    // Reload books
                    const reload = await fetch('<?= url('/api/upload/books') ?>');
                    const reloadData = await reload.json();
                    if (reloadData.books) this.books = reloadData.books;
                    setTimeout(() => {
                        this.uploadModal.show = false;
                        this.form.title = '';
                        this.form.slug = '';
                        this.form.file = null;
                        this.form.cover = null;
                        this.form.uploadProgress = 0;
                        this.form.success = '';
                    }, 1000);
                } else {
                    this.form.error = data.error || 'ອັບໂຫຼດລົ້ມເຫຼວ';
                    this.form.uploadProgress = 0;
                }
            } catch (e) {
                this.form.error = 'ເກີດຂໍ້ຜິດພາດ: ' + e.message;
                this.form.uploadProgress = 0;
            }
            this.form.uploading = false;
        },

        editBook(index) {
            const b = this.books[index];
            this.editModal.index = index;
            this.editModal.slug = b.slug;
            this.editModal.title = b.title || b.slug;
            this.editModal.year = b.year || '';
            this.editModal.error = '';
            this.editModal.success = '';
            this.editModal.show = true;
        },

        async saveEdit() {
            this.editModal.error = '';
            this.editModal.success = '';
            this.editModal.saving = true;

            const fd = new FormData();
            fd.append('slug', this.editModal.slug);
            fd.append('title', this.editModal.title);
            fd.append('year', this.editModal.year);

            try {
                const res = await fetch('<?= url('/upload/update') ?>', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    this.editModal.success = 'ບັນທຶກສຳເລັດ';
                    // Update local data
                    if (this.books[this.editModal.index]) {
                        this.books[this.editModal.index].title = this.editModal.title;
                        this.books[this.editModal.index].year = parseInt(this.editModal.year) || 0;
                    }
                    setTimeout(() => { this.editModal.show = false; }, 800);
                } else {
                    this.editModal.error = data.error || 'ບັນທຶກລົ້ມເຫຼວ';
                }
            } catch (e) {
                this.editModal.error = 'ເກີດຂໍ້ຜິດພາດ: ' + e.message;
            }
            this.editModal.saving = false;
        },

        uploadCover(index) {
            const b = this.books[index];
            this.coverModal.index = index;
            this.coverModal.slug = b.slug;
            this.coverModal.title = b.title || b.slug;
            this.coverModal.file = null;
            this.coverModal.error = '';
            this.coverModal.success = '';
            this.coverModal.show = true;
        },

        async saveCover() {
            if (!this.coverModal.file) {
                this.coverModal.error = 'ກະລຸນາເລືອກຮູບ';
                return;
            }
            this.coverModal.error = '';
            this.coverModal.success = '';
            this.coverModal.uploading = true;

            const fd = new FormData();
            fd.append('slug', this.coverModal.slug);
            fd.append('cover', this.coverModal.file);

            try {
                const res = await fetch('<?= url('/upload/upload-cover') ?>', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    this.coverModal.success = 'ອັບໂຫຼດຮູບປົກສຳເລັດ';
                    setTimeout(() => { this.coverModal.show = false; }, 800);
                } else {
                    this.coverModal.error = data.error || 'ອັບໂຫຼດລົ້ມເຫຼວ';
                }
            } catch (e) {
                this.coverModal.error = 'ເກີດຂໍ້ຜິດພາດ: ' + e.message;
            }
            this.coverModal.uploading = false;
        },

        deleteBook(slug, title) {
            if (!confirm('ຕ້ອງການລຶບ "' + title + '" ບໍ່?\nຂໍ້ມູນຈະຖືກລຶບຖິ້ມໝົດ')) return;

            fetch('<?= url('/upload/destroy') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slug: slug })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.books = this.books.filter(b => b.slug !== slug);
                } else {
                    alert(data.error || 'ລຶບລົ້ມເຫຼວ');
                }
            })
            .catch(e => alert('ເກີດຂໍ້ຜິດພາດ: ' + e.message));
        }
    };
}
</script>

<script id="files-books-data" type="application/json"><?= json_encode(array_map(function ($b) {
    $slug = $b['slug'];
    $assetsDir = __DIR__ . '/../../../public/assets';
    $bookDir = $assetsDir . '/' . $slug;
    $infoPath = $bookDir . '/book.json';
    $title = $b['slug'];
    if (file_exists($infoPath)) {
        $meta = json_decode(file_get_contents($infoPath), true);
        $title = $meta['title'] ?? $title;
    }
    $b['title'] = $title;
    // Detect cover extension
    $coverUrl = '';
    foreach (['png', 'jpg', 'jpeg'] as $ext) {
        if (file_exists($bookDir . '/cover.' . $ext)) {
            $coverUrl = url('/assets/' . $slug . '/cover.' . $ext);
            break;
        }
    }
    $b['coverUrl'] = $coverUrl;
    return $b;
}, $books), JSON_UNESCAPED_UNICODE) ?></script>

<style>
.page-enter { animation: fadeUp .35s ease-out both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>
