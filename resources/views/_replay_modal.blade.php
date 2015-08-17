<div class="modal-content">
    <h4>Watch Replay</h4>
    <ul class="collapsible" data-collapsible="accordion">
        <li>
            <div class="collapsible-header">Windows Command</div>
            <div class="collapsible-body modal-collapse-body">
                <p>Open a command prompt, paste this into it and press enter. Make sure your League of Legends client is running.</p>
                <div style="display: flex">
                    <textarea id="{{ $windowsCommandId }}" rows="1" readonly class="command-area" onclick="this.focus();this.select()">{{ $windowsCommand }}</textarea>
                    <i class="mdi-content-content-copy copy-button" data-copy-element="{{ $windowsCommandId }}" data-zclip-path="{{ asset('build/js/ZeroClipboard.swf') }}"></i>
                </div>
            </div>
        </li>
        <li>
            <div class="collapsible-header">Windows Batch File</div>
            <div class="collapsible-body modal-collapse-body">
                <a class="btn waves-effect waves-light red" href="{{ $batchLink }}"><i class="mdi-file-file-download left"></i> Download</a>
            </div>
        </li>
        <li>
            <div class="collapsible-header">Mac Command</div>
            <div class="collapsible-body modal-collapse-body">
                <p>Open a terminal window, paste this into it and press enter. Make sure your League of Legends client is running.</p>
                <div style="display: flex">
                    <textarea id="{{ $macCommandId }}" rows="1" readonly class="command-area" onclick="this.focus();this.select()">{{ $macCommand }}</textarea>
                    <i class="mdi-content-content-copy copy-button" data-copy-element="{{ $macCommandId }}" data-zclip-path="{{ asset('build/js/ZeroClipboard.swf') }}"></i>
                </div>
            </div>
        </li>
    </ul>

</div>
<div class="modal-footer">
    <a href="#" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
</div>