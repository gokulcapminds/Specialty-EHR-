<!-- public/modules/messaging.php -->
<link rel="stylesheet" href="css/modules/messaging.css?v=<?= time() ?>">
<div class="app-container">
    <?php $activeNav = 'messaging'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content mod-messaging-style-1">
        <header class="workspace-header mod-messaging-style-2" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1 class="mod-messaging-style-3">Messages</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <div class="chat-layout mod-messaging-style-4">
            
            <!-- Left Sidebar: Conversations -->
            <div class="chat-sidebar mod-messaging-style-5">
                <div class="mod-messaging-style-6">
                    <h3 class="mod-messaging-style-7">Practice Chat</h3>
                    <label class="mod-messaging-style-8">Recipient Staff Member</label>
                    <select id="chat-new-recipient" class="form-control mod-messaging-style-9" aria-label="messaging field 1">
                        <option value="general">🌐 General (Global Group Chat)</option>
                        <!-- Other users injected here via JS -->
                    </select>
                </div>
                
                <div class="mod-messaging-style-10">
                    <label class="mod-messaging-style-11">Conversations</label>
                </div>

                <div class="conversations-list mod-messaging-style-12" id="conversations-list">
                    <div class="mod-messaging-style-13">Loading...</div>
                </div>
            </div>

            <!-- Right Area: Chat Window -->
            <div class="chat-window mod-messaging-style-14">
                
                <!-- Chat Header -->
                <div class="chat-header mod-messaging-style-15">
                    <div class="chat-header-avatar mod-messaging-style-16" id="chat-header-avatar">G</div>
                    <div>
                        <h2 class="mod-messaging-style-17" id="chat-header-name">General</h2>
                        <span class="mod-messaging-style-18" id="chat-header-role">Practice-wide group conversation for all team members</span>
                    </div>
                </div>

                <!-- Chat Messages Area -->
                <div class="chat-messages mod-messaging-style-19" id="chat-messages-area">
                    <div class="mod-messaging-style-20">Select a conversation to start chatting.</div>
                </div>

                <!-- Bottom Area Container (Input + Dropdown) -->
                <div class="mod-messaging-style-21">
                    <!-- @Mention Dropdown (hidden by default) -->
                    <div class="mod-messaging-style-22" id="mention-dropdown">
                        <div class="mod-messaging-style-23">
                            <span class="mod-messaging-style-24">Mention a team member</span>
                        </div>
                        <div class="mod-messaging-style-25" id="mention-dropdown-list"></div>
                    </div>

                    <!-- Attachment Preview Area -->
                    <div class="mod-messaging-style-26" id="attachment-preview-bar">
                        <div class="mod-messaging-style-27" id="attachment-preview-list"></div>
                    </div>

                    <!-- Chat Input Area -->
                    <div class="chat-input-area mod-messaging-style-28">
                        <form class="mod-messaging-style-29" id="chat-form" >
                            <label class="mod-messaging-style-30" for="chat-attachment" id="chat-attachment-label" title="Attach file">
                                <i class="fas fa-paperclip"></i>
                                <input class="mod-messaging-style-31" type="file" id="chat-attachment" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                            </label>

                            <div class="mod-messaging-style-32">
                                <input class="mod-messaging-style-33" type="text" id="chat-input-text" placeholder="Type your message here... (@ to mention)" autocomplete="off" aria-label="Type your message here... (@ to mention)">
                            </div>

                            <button class="mod-messaging-style-34" type="submit" id="chat-send-btn" scale(1.1)'" scale(1)'">
                                <i class="fas fa-paper-plane mod-messaging-style-35"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

