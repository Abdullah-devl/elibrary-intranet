<footer class="w-full py-6 bg-slate-900 text-slate-400 text-xs mt-auto border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col lg:flex-row justify-between items-center gap-8 lg:gap-6">
        
        <!-- حقوق النسخ واسم المطور -->
        <div class="flex flex-col items-center lg:items-start gap-3 w-full lg:w-auto">
            <div class="text-center lg:text-right leading-relaxed">
                جميع الحقوق محفوظة للمكتبة المحلية &copy; {{ date('Y') }} - <span class="font-bold text-white">{{ $settings->name ?? 'المكتبة الإلكترونية' }}</span>
            </div>
            
            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-4 gap-y-2 text-slate-400 bg-slate-800/60 py-2 px-4 sm:px-5 rounded-xl shadow-inner border border-slate-700/50 w-full sm:w-auto">
                <span class="whitespace-nowrap">تطوير: <span class="text-slate-100 font-bold text-sm">م.عبدالله الحامد</span></span>
                
                <div class="hidden sm:block h-4 w-px bg-slate-600"></div>
                
                <div class="flex items-center gap-5">
                    <a href="https://wa.me/967774668633" target="_blank" class="text-slate-400 hover:text-green-400 transition-all duration-300 text-lg sm:text-xl hover:scale-110 hover:-translate-y-0.5" title="مراسلة المطور عبر واتساب">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a href="tel:774668633" class="text-slate-400 hover:text-blue-400 transition-all duration-300 text-base sm:text-lg hover:scale-110 hover:-translate-y-0.5" title="الاتصال بالمطور هاتفياً">
                        <i class="fa-solid fa-phone"></i>
                    </a>
                    <a href="mailto:bdallhalhamd656@gmail.com" class="text-slate-400 hover:text-red-400 transition-all duration-300 text-lg sm:text-xl hover:scale-110 hover:-translate-y-0.5" title="إرسال بريد للمطور">
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- رابط لوحة التحكم -->
        <div class="flex items-center justify-center w-full lg:w-auto border-t border-slate-800 pt-4 lg:border-0 lg:pt-0">
            <a href="{{ route('settings.index') ?? '#' }}" class="text-slate-400 hover:text-white flex items-center justify-center gap-2 transition-all duration-300 bg-slate-800 hover:bg-slate-700 py-2.5 px-6 rounded-xl border border-slate-700/50 w-full sm:w-auto shadow-sm group" title="إعدادات المشرف">
                <i class="fa-solid fa-gear group-hover:rotate-90 transition-transform duration-500"></i>
                <span class="font-bold text-sm">لوحة التحكم</span>
            </a>
        </div>
        
    </div>
</footer>
