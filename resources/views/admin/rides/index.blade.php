@extends('admin::layouts.admin_template')
@section('content')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="module">
        import {
            initializeApp
        } from 'https://www.gstatic.com/firebasejs/11.2.0/firebase-app.js'
        import {
            getFirestore,
            collection,
            doc,
            query,
            orderBy,
            getDocs,
            addDoc,
            onSnapshot,
            where,
            updateDoc,
            collectionGroup,
            limit
        } from 'https://www.gstatic.com/firebasejs/11.2.0/firebase-firestore.js'

        const firebaseConfig = {
            apiKey: "AIzaSyBIAPjj7Dtz3YrB4-8ot_1bCFeRroGAXjo",
            authDomain: "hopon-f5697.firebaseapp.com",
            projectId: "hopon-f5697",
            storageBucket: "hopon-f5697.firebasestorage.app",
            messagingSenderId: "298406021134",
            appId: "1:298406021134:web:8bb3359f107d906bd0d45f",
            measurementId: "G-MNDGJ1TN9H"
        };

        import {
            getMessaging,
            getToken,
            onMessage,
        } from "https://www.gstatic.com/firebasejs/11.2.0/firebase-messaging.js";
        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const messaging = getMessaging(app);
        $('.rpanel-title').on('click', function() {
            $('.user-section').show();
        });
        $('.send-btn').on('click', function() {
            var userId = $(this).data('user-id');
            var message = $('#message_input').val();
            var rideIdMessage = $('#ride_id_message').val();
            if (message.trim() !== '') {
                sendMessage(rideIdMessage, userId, message);
                $('#message_input').val('');
            }
        });

        $('#back-to-users').on('click', function() {
            $('.chat-section').hide();
            $('.user-section').show();
            $(this).addClass('d-none');
        });

        $('#back-to-users').on('click', function() {
            $('.chat-section').hide();
            $('.user-section').show();
            $(this).addClass('d-none');
        });

        // Adjust layout on window resize
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('.user-section').show();
                $('.chat-section').show();
                $('#back-to-users').addClass('d-none');
            } else {
                if ($('.user-section').is(':visible')) {
                    $('.chat-section').hide();
                } else {
                    $('.user-section').hide();
                }
            }
        }).trigger('resize');

        async function loadMessages(rideId, userId) {
            document.querySelector(".messages").innerHTML =
                `<div class="chat-loader"><span></span><span></span><span></span></div>`;
            const loginAdminId = "admin123";
            const chatId = `${rideId}_${userId}`;
            const messagesQuery = query(
                collection(db, "rideChats", chatId, "messages"),
                orderBy("createdAt", "asc")
            );

            try {
                onSnapshot(messagesQuery, (querySnapshot) => {
                    if (querySnapshot.empty) {
                        document.querySelector(".messages").innerHTML =
                            `<h6 style="color:black"> No message was found for this user </h6>`;
                        return;
                    }

                    let chatHTML = "";
                    let lastDate = null;

                    querySnapshot.forEach((doc) => {
                        const messageData = doc.data();
                        const messageDate = new Date(messageData.createdAt.seconds *
                            1000);

                        // Format Date as "Feb 03, 2025"
                        const formattedDate = messageDate.toLocaleDateString("en-US", {
                            year: "numeric",
                            month: "short",
                            day: "2-digit"
                        });
                        // Insert Date Header if it's a new day
                        if (formattedDate !== lastDate) {
                            chatHTML +=
                                `<h6 class="text-center text-muted">${formattedDate}</h6>`;
                            lastDate = formattedDate;
                        }
                        // Format Time as "HH:MM:SS"
                        const formattedTime = messageDate.toLocaleTimeString("en-US", {
                            hour: "2-digit",
                            minute: "2-digit",
                            hour12: false,
                        });
                        // Check sender and set message styles
                        if (messageData.senderId === loginAdminId) {
                            chatHTML += `<div class="d-flex mb-3">
                                <div class="bg-success text-white p-2 rounded ms-auto">
                                    <p style="word-wrap: break-word; word-break: break-word; white-space: normal; overflow-wrap: break-word;">
                                        ${messageData.text}
                                    </p>
                                    <small class="text-white-50">${formattedTime}</small>
                                </div>
                            </div>`;
                        } else {
                            chatHTML += `<div class="d-flex mb-3">
                                <div class="bg-primary text-white p-2 rounded me-auto">
                                    <small class=" ${messageData.senderName ? 'text-warning' : 'text-danger'} ">${messageData.senderName ? 'passenger' : 'driver'}</small>
                                    <p style="word-wrap: break-word; word-break: break-word; white-space: normal; overflow-wrap: break-word;">
                                        ${messageData.text}
                                    </p>
                                    <small class="text-white-50">${formattedTime}</small>
                                </div>
                            </div>`;
                        }
                    });
                    document.querySelector(".messages").innerHTML = chatHTML;
                    // Scroll to the bottom of the chat container
                    const chatContainer = document.querySelector(".messages");
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                });
            } catch (error) {
                console.error("Error loading messages:", error);
            }
        }


        function clickChatLoadButton() {
            const button = document.querySelector('.chat-load-btn');
            if (button) {
                button.click();
            } else {
                setTimeout(clickChatLoadButton, 1000);
            }
        }
        clickChatLoadButton();

        // Attach event listener using delegation
        $(document).on("click", ".chat-load-btn", function() {
            const rideId = $(this).data("ride-id");
            const userId = $(this).data("user-id");
            $('.send-btn').attr('data-user-id', userId);
            loadMessages(rideId, userId);
        });

        function listenToRideChats(rideId, driverId) {
            $(".box-sendMessage").addClass('d-none');
            if (!rideId || !driverId) return;
            const chatsQuery = query(
                collection(db, "rideChats"),
                where("rideId", "==", rideId),
                where("driverId", "==", driverId)
            );
            const unsubscribe = onSnapshot(chatsQuery, async (snapshot) => {
                const chatDataPromises = snapshot.docs.map(async (docSnap) => {
                    const chatData = docSnap.data();
                    const messagesRef = collection(db, "rideChats", docSnap.id, "messages");
                    const latestMessageQuery = query(messagesRef, orderBy("createdAt",
                        "desc"));
                    const messagesSnap = await getDocs(latestMessageQuery);
                    const latestMessageDoc = messagesSnap.docs[0];
                    if (!latestMessageDoc) return null;
                    const msgData = latestMessageDoc.data();
                    return {
                        chatId: docSnap.id,
                        lastMessage: msgData.text || '',
                        userId: chatData.userId,
                        userName: msgData.senderName || chatData.senderName || 'Passenger',
                        createdAt: msgData.createdAt?.toDate() || new Date(),
                    };
                });
                const resolvedChatList = await Promise.all(chatDataPromises);
                const filteredChatList = resolvedChatList
                    .filter(item => item !== null)
                    .filter((item, index, self) =>
                        index === self.findIndex(t => t.userId === item.userId)
                    );
                filteredChatList.sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
                if (filteredChatList.length > 0) {
                    $(".box-sendMessage").removeClass('d-none');
                }
                // Clear old buttons first if needed:
                $(".media-list").empty();
                for (const data of filteredChatList) {
                    $(".media-list").append(`
                        <button 
                            class="btn btn-primary mb-1 chat-load-btn" 
                            data-ride-id="${rideId}" 
                            data-user-id="${data.userId}">
                            ${data.userName}
                        </button>
                    `);
                }

                clickChatLoadButton()

            });
            return unsubscribe;
        }




        function getUnreadMessageCount(userId) {
            const loginAdminId = "admin123";
            const chatId = `u_${userId}_a_${loginAdminId}`;
            const unreadMessagesQuery = query(
                collection(db, "Chats", chatId, "messages"),
                where("reciver_Id", "==", "a_" + loginAdminId), // Messages for the admin
                where("isRead", "==", false) // Only unread messages
            );

            // Real-time listener for unread messages
            onSnapshot(unreadMessagesQuery, (querySnapshot) => {
                const unreadCount = querySnapshot.size;
                const unreadBadge = document.querySelector(`.unread-count_${userId}`);
                const unreadBadgeList = document.querySelector(`.unread-message-count_${userId}`);
                unreadCount > 0 ? unreadBadge.classList.remove('d-none') : unreadBadge.classList.add(
                    'd-none');
                if (unreadBadgeList) {

                    unreadCount > 0 ? unreadBadgeList.classList.remove('d-none') : unreadBadgeList
                        .classList.add(
                            'd-none');
                }
                if (unreadBadge) {
                    unreadBadge.textContent = unreadCount;
                }

            });
        }
        document.querySelectorAll('.user-item').forEach(async (element) => {
            const userid = element.getAttribute('data-user-id');
            const unreadCount = await getUnreadMessageCount(userid);
        });

        function markMessagesAsRead(userId) {
            const loginAdminId = "admin123";
            const chatId = `u_${userId}_a_${loginAdminId}`;
            const unreadMessagesQuery = query(
                collection(db, "Chats", chatId, "messages"),
                where("reciver_Id", "==", "a_" + loginAdminId), // Messages for the admin
                where("isRead", "==", false) // Only unread messages
            );
            $("#unreadMessageCount").addClass('d-none');
            // Update isRead to true for all unread messages
            getDocs(unreadMessagesQuery).then((querySnapshot) => {
                querySnapshot.forEach((doc) => {
                    const messageRef = doc.ref;
                    updateDoc(messageRef, {
                        isRead: true
                    });
                });
            }).catch((error) => {});
        }

        function getUnreadMessageCountAll() {
            const loginAdminId = "admin123";
            const unreadMessagesQuery = query(
                collectionGroup(db, "messages"),
                where("isRead", "==", false),
                where("reciver_Id", "==", "a_" + loginAdminId)
            );

            onSnapshot(
                unreadMessagesQuery,
                (querySnapshot) => {
                    querySnapshot.forEach((doc) => {
                        console.log(doc.data());
                    });

                    const unreadCount = querySnapshot.size;
                    const unreadBadge = document.getElementById("unreadMessageCount");

                    if (unreadBadge) {
                        unreadCount > 0 ? $("#unreadMessageCount").removeClass('d-none') : $(
                                "#unreadMessageCount")
                            .addClass('d-none');
                        unreadBadge.textContent = unreadCount > 0 ? unreadCount : "";
                    }
                },
                (error) => {
                    console.error("Error fetching unread messages:", error);
                }
            );
        }

        getUnreadMessageCountAll()

        function sendMessage(rideId, userId, message) {
            const loginAdminId = "admin123";
            const chatId = `${rideId}_${userId}`;
            const newMessage = {
                text: message,
                createdAt: new Date(), // Use Firestore server timestamp
                senderId: loginAdminId,
                senderName: 'Admin',
                receiver_id: userId,
                senderAvatar: 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                _id: Math.random().toString(36).substr(2, 9), // Generate unique ID
                isRead: false,
            };
            const chatHTML = `
            <div class="d-flex mb-3">
                <div class="bg-success text-white p-2 rounded ms-auto">
                    <strong>You:</strong> ${message}<br>
                    <small class="text-white-50">${new Date().toLocaleTimeString()}</small>
                </div>
            </div>`;
            $(".messages").append(chatHTML);
            scrollToBottom();
            const messagesRef = collection(db, "rideChats", chatId, "messages");
            addDoc(messagesRef, newMessage)
                .then((docRef) => {
                    console.log("hello")
                })
                .catch((error) => {});

        }

        // Auto-scroll to bottom when new messages arrive
        function scrollToBottom() {
            const messagesContainer = document.querySelector('.messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        scrollToBottom();


        document.addEventListener('DOMContentLoaded', function() {
            let dropdownVisible = false;
            const dropdownMenu = document.getElementById('custom-notification-dropdown');
            // Attach event listener to all buttons
            document.querySelectorAll('.loadMessagesBtn').forEach(button => {
                $(".media-list").empty();
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent the document click event
                    dropdownVisible = true;
                    // Optional: extract rideId and userId
                    const rideId = this.getAttribute('data-ride-id');
                    const userId = this.getAttribute('data-user-id');
                    document.querySelector(".messages").innerHTML =
                        `<h6 style="color:black"> No message was found for this user </h6>`;
                    // Load messages based on data
                    $("#ride_id_message").val(rideId);
                    listenToRideChats(rideId, userId);


                    // Show dropdown
                    dropdownMenu.classList.add('show');
                });
            });
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const toggleButtons = document.querySelectorAll('.loadMessagesBtn');
                let isClickInsideAnyButton = false;
                toggleButtons.forEach(btn => {
                    if (btn.contains(event.target)) {
                        isClickInsideAnyButton = true;
                    }
                });
                if (!isClickInsideAnyButton && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove('show');
                    dropdownVisible = false;
                }
            });
        });
    </script>
    <style>
        /* Ensure the chat container stays on top of everything */
        .floating-chat-container {
            position: fixed !important;
            bottom: 20px !important;
            right: 20px !important;
            z-index: 1050 !important;
        }

        /* Override any Bootstrap dropdown positioning */
        .floating-chat-container .dropdown-menu {
            position: absolute !important;
            transform: none !important;
            inset: auto auto auto auto !important;
            bottom: calc(100% + 10px) !important;
            top: auto !important;
            right: 0 !important;
            left: auto !important;
            margin: 0 !important;
        }

        /* Make sure the dropdown doesn't get hidden */
        .chat-dropdown.show {
            display: flex !important;
            flex-direction: column;
        }

        /* Chat content area */
        .chat-content {
            display: flex;
            height: 500px;
        }

        /* User list styling */
        .media-list {
            width: 25%;
            overflow-y: auto;
            border-right: 1px solid #eee;
            display: block !important;
            /* Force show by default */
        }

        /* Chat section styling */
        .chat-section {
            width: 75%;
            display: flex;
            flex-direction: column;
        }

        /* Message area styling */
        .messages {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
            overflow-y: auto;
            flex: 1;
        }

        .messages::-webkit-scrollbar {
            width: 6px;
        }

        .messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .messages::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 3px;
        }
    </style>
    <div class="list-grid-nav hstack gap-1 mb-3">
        <div class="selected-action" style="display:inline-block;position:relative;">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                    class="fa fa-check-square-o"></i> Bulk Actions</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0)" data-name="active" title="Active Selected"><i
                            class="fa fa-check"></i> Active Selected</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" data-name="inactive" title="Inactive Selected"><i
                            class="fa fa-times"></i> Inactive Selected</a></li>
                <li><a class="dropdown-item text-danger" href="javascript:void(0)" data-name="delete"
                        title="Delete Selected"><i class="fa fa-trash"></i> Delete Selected</a></li>
            </ul>
        </div>
    </div>

    <div class="floating-chat-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050;">
        <div class="dropdown-menu chat-dropdown p-0"
            style="width: 600px; max-height: 70vh; transform: translateY(0) !important; bottom: calc(100% + 10px) !important; top: auto !important; right: 0 !important; left: auto !important;"
            id="custom-notification-dropdown">
            <div class="dropdown-head bg-primary rounded-top" style="background-color:#eb663d !important;">
                <div class="p-3">
                    <div class="row align-items-center">
                        <input type="hidden" name="ride_id_message" id="ride_id_message">
                        <div class="col">
                            <h6 class="m-0 fs-16 fw-semibold text-white">Messages</h6>
                        </div>
                        <div class="col-auto">
                            <span class="badge badge-soft-light fs-13 notification_count"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-content" style="height: 500px; display: flex;">
                <!-- User list on left (25% width) -->
                <div class="media-list user-section p-2"
                    style="width: 25%; overflow-y: auto; border-right: 1px solid #eee; display: block;">
                    <!-- User buttons will be added here -->
                </div>

                <!-- Chat section on right (75% width) -->
                <div class="chat-section d-flex flex-column" style="width: 75%;">
                    <button id="back-to-users" class="btn btn-sm btn-light mb-2 ms-2 align-self-start d-none">
                        <i class="fa fa-arrow-left me-1"></i> Back to users
                    </button>

                    <div class="messages p-2" style="overflow-y: auto; flex: 1; background: #f9f9f9;">
                        <h6 class="text-muted text-center my-4">Select a conversation to start chatting</h6>
                    </div>

                    <div class="input-group p-2 box-sendMessage d-none">
                        <input type="text" class="form-control message-input" id="message_input"
                            placeholder="Type your message...">
                        <button type="button" class="btn btn-primary send-btn">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="card">
        <div class="card-header align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1">{{ $page_title }}</h4>
            <div class="box-tools pull-right" style="position: relative;margin-top: -5px;margin-right: -10px">

                <form method="get" style="display:inline-block;width: 290px;" action="{{ route('getManageRide') }}">
                    <div class="input-group">
                        <input type="text" name="q" value="{{ request()->get('q') }}"
                            class="form-control rounded-0 pull-right" placeholder="Search">

                        <div class="input-group-btn">
                            @if (!empty(request()->get('q')))
                                <button type="button" onclick="location.href='{{ route('getManageRide') }}'" title="Reset"
                                    class="btn rounded-0 btn-warning"><i class="fa fa-ban"></i></button>
                            @endif
                            <button type="submit" class="btn rounded-0 btn-primary me-2"><i
                                    class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>


                <form method="get" id="form-limit-paging" style="display:inline-block"
                    action="{{ route('getManageRide') }}">
                    @php $limis =[5,10,20,25,50,100,200]; @endphp
                    <div class="input-group">
                        <select onchange="$('#form-limit-paging').submit()" name="limit" style="width: 56px;"
                            class="form-control input-sm">
                            @foreach ($limis as $lmt)
                                <option value="{{ $lmt }}" {{ $lmt == $limit ? 'selected' : '' }}>
                                    {{ $lmt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

            </div>

            <br style="clear:both">

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <form id="form-table" method="post" action="{{ route('getManageRide') }}/action-selected">
                    <input type='hidden' name='button_name' value='' />
                    @csrf
                    <table id="table_dashboard" class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-blue">
                            <tr class="active">
                                <th width="3%"><input type="checkbox" id="checkall"></th>
                                <th width="auto"><a href="{{ route('getManageRide') }}" title="Click to sort">Driver
                                        Name
                                </th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=origin&sorting={{ request()->get('filter_column') == 'origin' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Origin &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=destination&sorting={{ request()->get('filter_column') == 'destination' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Destination &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=available_seats&sorting={{ request()->get('filter_column') == 'available_seats' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Available Seats &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=departure_time&sorting={{ request()->get('filter_column') == 'departure_time' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Departure Time &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=fare_per_seat&sorting={{ request()->get('filter_column') == 'fare_per_seat' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Fare Per Seat &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=status&sorting={{ request()->get('filter_column') == 'status' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Status &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto"><a
                                        href="{{ route('getManageRide') }}?filter_column=created_at&sorting={{ request()->get('filter_column') == 'created_at' && request()->get('sorting') == 'asc' ? 'desc' : 'asc' }}"
                                        title="Click to sort">Created At &nbsp; <i class="fa fa-sort"></i></a></th>
                                <th width="auto" style="text-align:right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($rows) && count($rows))
                                @foreach ($rows as $data)
                                    <tr>
                                        <td><input type="checkbox" class="checkbox" name="checkbox[]"
                                                value="{{ $data->id }}"></td>
                                        <td>{{ $data->driverDetails->name }}</td>
                                        <td>{{ $data->origin }}</td>
                                        <td>{{ $data->destination }}</td>
                                        <td>{{ $data->available_seats }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->departure_time)->format('d-m-Y H:s') }}</td>
                                        <td>{{ $data->fare_per_seat }}</td>
                                        <td>
                                            @if ($data->status == 1)
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($data->status == 2)
                                                <span class="badge bg-success">Confirmed</span>
                                            @elseif($data->status == 4)
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </td>
                                        <td>{{ date('m-d-Y', strtotime($data->created_at)) }}</td>
                                        <td>
                                            <div class="button_action" style="text-align:right">
                                                <button type="button"
                                                    class="loadMessagesBtn btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                                    id="custom-notification-toggle" aria-haspopup="true"
                                                    aria-expanded="false" data-ride-id="{{ $data->id }}"
                                                    data-user-id="{{ $data->driver_id }}">
                                                    <i class='bx bx-message fs-22'></i>
                                                </button>

                                                <a class="btn btn-sm btn-primary btn-detail" title="Detail Data"
                                                    href="{{ route('getManageRideDetails', $data->id) }}?return_url={{ route('getManageRide') }}"><i
                                                        class="fa fa-eye"></i></a>
                                                <a class="btn btn-sm btn-success btn-edit" title="Edit Data"
                                                    @if ($data->status != 3 && $data->status != 4) href="{{ route('getEditRide', $data->id) }}?return_url={{ route('getManageRide') }}"
                                                    @else
                                                        href="#" @endif>
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a class="btn btn-sm btn-warning btn-delete" title="Delete"
                                                    href="javascript:;"
                                                    onclick="Swal.fire({
                                    title: 'Are you sure ?',   
                                    text: 'You will not be able to recover this record data!',  
                                    icon: 'warning',
                                    showCancelButton: !0,
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'No, cancel!',
                                    confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                                    cancelButtonClass: 'btn btn-danger w-xs mt-2',
                                    buttonsStyling: !1,
                                    showCloseButton: !0,
                                }).then(function (t) {
                                    t.isConfirmed?location.href='{{ route('deleteUser', $data->id) }}':''});">
                                                    <i class="fa fa-trash"></i>
                                                </a>


                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" style="text-align:center"><i class="fa fa-search"></i> No Data
                                        Avaliable</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <span>Total rows : {{ $rows->total() }}</span>
                </div>
                <div class="col-md-8">
                    <div class="pull-right">{!! $rows->withQueryString()->links('pagination::bootstrap-4') !!} </div>
                </div>
            </div>
        </div>
    </div>
@endsection
