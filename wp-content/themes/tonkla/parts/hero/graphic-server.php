<?php defined( 'ABSPATH' ) || exit; ?>
<div class="bg-slate-800 rounded-xl p-4 border border-slate-600">
    <div class="flex items-center gap-2 mb-4 border-b border-slate-600 pb-4">
        <div class="w-3 h-3 rounded-full bg-red-500"></div>
        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
        <div class="w-3 h-3 rounded-full bg-green-500"></div>
        <span class="ml-2 text-xs text-slate-400 font-mono">root@tonkla-server:~#</span>
    </div>
    <div class="space-y-3 font-mono text-sm">
        <div class="flex items-center gap-3">
            <span class="text-brand">➜</span>
            <span class="text-sky-300">~</span>
            <span class="text-slate-300">htop</span>
        </div>
        <div class="grid grid-cols-2 gap-4 text-xs text-slate-400 mt-2">
            <div class="bg-slate-900/50 p-2 rounded">
                <div class="flex justify-between mb-1"><span>CPU Usage</span> <span class="text-brand">12.5%</span>
                </div>
                <div class="w-full bg-slate-700 h-1.5 rounded-full">
                    <div class="bg-brand h-1.5 rounded-full" style="width: 12.5%"></div>
                </div>
            </div>
            <div class="bg-slate-900/50 p-2 rounded">
                <div class="flex justify-between mb-1"><span>Memory</span> <span class="text-blue-400">4.2/16 GB</span>
                </div>
                <div class="w-full bg-slate-700 h-1.5 rounded-full">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 26%"></div>
                </div>
            </div>
        </div>
    </div>
</div>