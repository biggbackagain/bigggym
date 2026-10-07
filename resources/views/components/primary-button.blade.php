<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#171A20] dark:bg-white border border-transparent rounded-[4px] font-medium text-[14px] text-white dark:text-[#171A20] hover:bg-[#393C41] dark:hover:bg-gray-200 focus:bg-[#393C41] dark:focus:bg-gray-200 active:bg-[#393C41] dark:active:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-[#171A20] dark:focus:ring-white focus:ring-offset-2 transition-colors duration-330']) }}>
    {{ $slot }}
</button>









