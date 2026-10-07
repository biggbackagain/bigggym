<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-6 py-2 bg-white dark:bg-[#000000] border border-[#EEEEEE] dark:border-[#333333] rounded-[4px] font-medium text-[14px] text-[#393C41] dark:text-gray-300 hover:bg-[#F4F4F4] dark:bg-[#111111] focus:bg-[#F4F4F4] dark:bg-[#111111] focus:outline-none focus:ring-1 focus:ring-[#3E6AE1] disabled:opacity-25 transition-colors duration-330']) }}>
    {{ $slot }}
</button>







