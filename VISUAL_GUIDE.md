# 📸 Visual Guide - SPC Voting System

This document describes the visual appearance and user interface of the SPC Voting System.

## 🎨 Color Scheme

### Primary Colors
- **Primary Blue**: `#4361ee` - Main brand color, buttons, links
- **Dark Blue**: `#3a0ca3` - Gradients, hover states
- **Secondary Pink**: `#f72585` - Event indicators, accents
- **Success Green**: `#2ec4b6` - Vote progress bars, success messages
- **Orange**: `#f59e0b` - Admin statistics

### Neutral Colors
- **Background**: `#f8f9fa` - Page background
- **White**: `#ffffff` - Cards, sidebar
- **Text Main**: `#2b2d42` - Primary text
- **Text Muted**: `#8d99ae` - Secondary text
- **Border**: `#e9ecef` - Dividers, card borders

---

## 🏠 Landing Page (/)

**Layout:**
```
┌─────────────────────────────────────┐
│                                     │
│     🎓 SPC Voting System           │
│                                     │
│   Welcome to San Pedro College      │
│   Voting & Event Management System  │
│                                     │
│      [ Get Started Button ]         │
│                                     │
└─────────────────────────────────────┘
```

**Features:**
- Full-screen gradient background (blue)
- Centered content
- Large heading with emoji
- White "Get Started" button
- Redirects to login page

---

## 🔐 Login Page (/login)

**Layout:**
```
┌──────────────────────────┐
│    [Graduation Cap]      │
│   SPC Voting System      │
│  Sign in to your account │
├──────────────────────────┤
│                          │
│  Email Address           │
│  [________________]      │
│                          │
│  Password                │
│  [________________]      │
│                          │
│  ☐ Remember Me           │
│                          │
│  [ Login Button ]        │
│                          │
│  Don't have an account?  │
│  Register here           │
│                          │
└──────────────────────────┘
```

**Features:**
- Centered modal-style card
- Gradient header (blue)
- White body with form
- Error messages shown in red box
- Link to registration page
- Rounded corners, shadow effect

---

## 📝 Registration Page (/register)

**Layout:**
```
┌──────────────────────────┐
│    [User Plus Icon]      │
│    Create Account        │
│ Join the SPC Voting Sys  │
├──────────────────────────┤
│                          │
│  Full Name               │
│  [________________]      │
│                          │
│  Email Address           │
│  [________________]      │
│                          │
│  Student ID (optional)   │
│  [________________]      │
│                          │
│  Department              │
│  [▼ Select Dept    ]     │
│                          │
│  Password                │
│  [________________]      │
│                          │
│  Confirm Password        │
│  [________________]      │
│                          │
│  [ Register Button ]     │
│                          │
│  Already have account?   │
│  Login here              │
│                          │
└──────────────────────────┘
```

**Features:**
- Scrollable content area
- Department dropdown with 5 options
- Password confirmation
- Validation error display
- Link back to login

---

## 👨‍🎓 Student Dashboard

### Sidebar (Left Side)
```
┌──────────────────┐
│ 🎓 SPC System    │
├──────────────────┤
│ □ Dashboard      │ (Blue when active)
│ □ Voting         │
│ □ Events         │
│ □ Logout         │
├──────────────────┤
│ [Avatar] Student │
│         IT Dept  │
└──────────────────┘
```

### Main Content Area
```
┌─────────────────────────────────────────────┐
│ Dashboard                                    │
│ Welcome back! Here's what's happening...    │
├─────────────────────────────────────────────┤
│                                             │
│ ┌──────┐ ┌──────┐ ┌──────┐                │
│ │  👥  │ │  📅  │ │  ✅  │                │
│ │  12  │ │   8  │ │  450 │                │
│ │Cands │ │Evts  │ │Votes │                │
│ └──────┘ └──────┘ └──────┘                │
│                                             │
│ Recent Events                               │
│ ┌─────────────────────────────────┐        │
│ │ 15  │ IT Hackathon 2024        │        │
│ │ NOV │ Join us for 24-hour...   │        │
│ │     │ 📍 IT                     │        │
│ └─────────────────────────────────┘        │
│ [More event cards...]                      │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🗳️ Voting Page

### Filter Bar
```
┌─────────────────────────────────────┐
│ [ All ] [ IT ] [ BSBA ] [ CRIM ]   │
│ [ EDUC ] [ ENGINEERING ]            │
└─────────────────────────────────────┘
```
- Pills with borders
- Active filter has blue background
- Horizontal scrollable on mobile

### Candidate Cards (Grid Layout)
```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   [Photo]    │ │   [Photo]    │ │   [Photo]    │
├──────────────┤ ├──────────────┤ ├──────────────┤
│ IT Dept      │ │ BSBA Dept    │ │ CRIM Dept    │
│ Juan DC      │ │ Maria S      │ │ Luis C       │
│ President    │ │ President    │ │ Rep          │
│              │ │              │ │              │
│ ▓▓▓▓░░░ 60%  │ │ ▓▓▓░░░░ 40%  │ │ ▓▓░░░░░ 25%  │
│ 120 votes    │ │ 95 votes     │ │ 60 votes     │
│              │ │              │ │              │
│ [ Vote ]     │ │ [ Vote ]     │ │ [ Vote ]     │
└──────────────┘ └──────────────┘ └──────────────┘
```

**Card Features:**
- Photo at top (200px height)
- Department badge
- Candidate name (large)
- Position (blue text)
- Progress bar (green)
- Vote count and percentage
- Vote button (white with blue border)
- "Voted" button (gray) after voting
- Hover effect (lift up)

---

## 📅 Events Page

### Header
```
┌────────────────────────────────────────┐
│ University Events                      │
│ Stay updated with seminars...      [+ Post Event] │
└────────────────────────────────────────┘
```

### Event Cards
```
┌─────────────────────────────────────┐
│ ┌───┐  IT Hackathon 2024           │
│ │15 │  Join us for a 24-hour       │
│ │NOV│  coding marathon!            │
│ └───┘  📍 IT  👤 Admin User        │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ ┌───┐  Business Ethics Seminar     │
│ │20 │  Guest speakers from top     │
│ │NOV│  corporations...             │
│ └───┘  📍 BSBA  👤 Admin User      │
└─────────────────────────────────────┘
```

**Event Card Features:**
- Date box (left side, pink background)
- Event title (bold)
- Description (2 lines max)
- Department icon
- Posted by information
- Pink left border

### Post Event Modal
```
┌──────────────────────────┐
│ Post New Event        ✕  │
├──────────────────────────┤
│                          │
│ Event Title              │
│ [__________________]     │
│                          │
│ Department               │
│ [▼ Select Dept    ]      │
│                          │
│ Date                     │
│ [📅 __/__/__]            │
│                          │
│ Description              │
│ [                    ]   │
│ [                    ]   │
│ [                    ]   │
│                          │
│ [ Publish Event ]        │
│                          │
└──────────────────────────┘
```

---

## 👨‍💼 Admin Dashboard

### Sidebar (Different Icon)
```
┌──────────────────┐
│ 🛡️ SPC Admin     │
├──────────────────┤
│ □ Dashboard      │
│ □ Candidates     │
│ □ Events         │
│ □ Students       │
│ □ Logout         │
├──────────────────┤
│ [Avatar] Admin   │
│    Administrator │
└──────────────────┘
```

### Dashboard Stats (4 Cards)
```
┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
│  👥  │ │  📅  │ │  ✅  │ │  🎓  │
│  12  │ │   8  │ │  450 │ │  156 │
│Cands │ │Evts  │ │Votes │ │Studs │
└──────┘ └──────┘ └──────┘ └──────┘
```

---

## 🎯 Manage Candidates Page (Admin)

### Header with Button
```
┌─────────────────────────────────────┐
│ Manage Candidates        [+ Add Candidate] │
│ Add, edit, or remove candidates     │
└─────────────────────────────────────┘
```

### Candidates Table
```
┌──────────────────────────────────────────────────┐
│ Name        │ Position  │ Dept │ Votes │ Actions│
├──────────────────────────────────────────────────┤
│ Juan DC     │ President │ IT   │ 120   │ [Delete]│
│ Maria S     │ President │ BSBA │  95   │ [Delete]│
│ Pedro R     │ VP        │ IT   │  80   │ [Delete]│
│ Ana G       │ Secretary │ EDUC │ 150   │ [Delete]│
└──────────────────────────────────────────────────┘
```

### Add Candidate Modal
```
┌──────────────────────────┐
│ Add New Candidate     ✕  │
├──────────────────────────┤
│ Name                     │
│ [__________________]     │
│                          │
│ Position                 │
│ [__________________]     │
│                          │
│ Department               │
│ [▼ Select Dept    ]      │
│                          │
│ Image URL (optional)     │
│ [https://...]            │
│                          │
│ [ Add Candidate ]        │
└──────────────────────────┘
```

---

## 📊 Students Page (Admin)

### Students Table
```
┌────────────────────────────────────────────────────────┐
│ Name     │ Email        │ Student ID │ Dept │ Registered│
├────────────────────────────────────────────────────────┤
│ Juan DC  │ juan@spc.edu │ 2024-00001 │ IT   │ Jan 1     │
│ Maria S  │ maria@spc... │ 2024-00002 │ BSBA │ Jan 1     │
└────────────────────────────────────────────────────────┘
```

---

## 🎨 UI Components

### Statistics Cards
- **Size**: Auto-fit grid, min 240px
- **Style**: White background, rounded corners, shadow
- **Icon**: 50px circle with colored background
- **Number**: Large font (1.5rem+)
- **Label**: Gray text below

### Filter Chips
- **Default**: White with gray border
- **Active**: Blue background, white text
- **Hover**: Blue background, white text
- **Shape**: Rounded pill (border-radius: 20px)

### Buttons

**Primary Button:**
- Background: Blue gradient
- Text: White
- Hover: Lifts up 2px
- Icon: Font Awesome icon + text

**Danger Button:**
- Background: Red (#dc3545)
- Text: White
- Size: Smaller than primary

**Vote Button:**
- Default: White with blue border
- Hover: Blue background, white text
- Voted: Gray, disabled

### Toast Notifications
```
┌─────────────────────────────┐
│ ✅ Voted successfully!      │
└─────────────────────────────┘
```
- Appears bottom-right
- Auto-dismiss after 3 seconds
- Slide in from right animation
- Green border for success
- Red border for errors

---

## 📱 Responsive Behavior

### Desktop (> 768px)
- Sidebar on left (260px)
- Main content on right
- Grid layout for cards
- Horizontal stats cards

### Mobile (< 768px)
- Sidebar collapses to horizontal bar
- Logo text hidden
- User profile hidden
- Vertical stacking
- Single column cards

---

## ⚡ Animations

1. **Page transitions**: Fade in from below (0.4s)
2. **Card hover**: Lift up 5px
3. **Button hover**: Lift up 2px
4. **Toast**: Slide in from right
5. **Progress bars**: Smooth width animation (1s)
6. **Modal**: Slide down from top

---

## 🎯 Accessibility Features

- Semantic HTML
- Proper heading hierarchy
- Alt text on images
- Focus states on inputs
- High contrast colors
- Readable font sizes
- Hover states
- Loading indicators

---

**Note:** All icons use Font Awesome 6.4 solid style. All fonts use Google Fonts' Poppins family (weights: 300, 400, 500, 600, 700).
