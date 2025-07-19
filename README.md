# 💰 BankNote Plugin v2.0.0

[![Poggit-CI](https://poggit.pmmp.io/ci.shield/GabBiswajit/BankNote/BankNote)](https://poggit.pmmp.io/ci/GabBiswajit/BankNote/BankNote)
[![](https://poggit.pmmp.io/shield.state/BankNote)](https://poggit.pmmp.io/p/BankNote)
[![](https://poggit.pmmp.io/shield.dl/BankNote)](https://poggit.pmmp.io/p/BankNote)

A secure and feature-rich banknote system for PocketMine-MP servers! Convert your money into physical items that can be traded, stored, and redeemed safely with advanced anti-duplication protection.

## 🆕 **What's New in v2.0.0**

### 🔥 **Major Updates:**
- **🆕 BedrockEconomy Support**: Modern async economy system (replacing libEco)
- **🛡️ Enhanced Security**: Advanced anti-duplication and exploit prevention
- **💎 Universal Item Support**: Use ANY Minecraft item via StringToItemParser
- **🎨 Improved UI**: Better FormAPI integration with fallback support
- **🔐 Professional Permissions**: Standardized `banknote.use` system
- **⚡ Performance Boost**: Async operations and optimized code
- **📋 Poggit Ready**: Full compliance with modern standards

### 🔄 **Breaking Changes:**
- **Economy System**: libEco → BedrockEconomy (auto-detection included)
- **Permissions**: `BankNote.note.cmd` → `banknote.use`
- **Configuration**: Enhanced config structure with more options

## ✨ **Core Features**

### 🛡️ **Advanced Security**
- **Anti-Duplication System**: Prevents exploit attempts and item duplication
- **NBT Validation**: Ensures banknote integrity and authenticity  
- **Block Interaction Filter**: Prevents conflicts with chests and item frames
- **Cooldown Protection**: Configurable spam prevention system
- **Item Validation**: Multi-layer verification for secure transactions

### 💎 **Flexible Item System**
- **StringToItemParser**: Support for ANY Minecraft item type
- **Custom Items**: Diamond, emerald, nether star, totem, and more
- **Visual Customization**: Color codes, custom names, and lore
- **Placeholder Support**: Dynamic {amount}, {creator}, {date} variables

### 💰 **Multi-Economy Support**
- **BedrockEconomy** (Primary) - Modern async operations
- **EconomyAPI** (Secondary) - Classic compatibility
- **Auto-Detection**: Seamless economy plugin detection
- **Fallback Support**: Works with either economy system

### 🎨 **Enhanced User Interface**
- **FormAPI Integration**: Beautiful GUI forms with quick amounts
- **Custom Amount Input**: Flexible amount entry system
- **Fallback UI**: Text-based interface when FormAPI unavailable
- **User-Friendly Messages**: Clear feedback and error handling

## 📋 **Requirements**

- **PocketMine-MP**: 5.0.0+
- **PHP**: 8.0+
- **Economy Plugin**: BedrockEconomy OR EconomyAPI
- **Optional**: FormAPI for enhanced GUI experience

## 🚀 **Installation**

1. Download from [Poggit](https://poggit.pmmp.io/p/BankNote)
2. Place the `.phar` file in your server's `plugins/` folder
3. Install an economy plugin:
   - **Recommended**: [BedrockEconomy](https://poggit.pmmp.io/p/BedrockEconomy)
   - **Alternative**: [EconomyAPI](https://poggit.pmmp.io/p/EconomyAPI)
4. **Optional**: Install [FormAPI](https://poggit.pmmp.io/p/FormAPI) for GUI support
5. Restart your server
6. Configure in `plugins/BankNote/config.yml`

## 🎯 **Commands & Permissions**

| Command | Description | Permission | Aliases |
|---------|-------------|------------|---------|
| `/banknote <amount>` | Create a banknote | `banknote.use` | `/bn`, `/note` |
| `/banknote ui` | Open GUI interface | `banknote.use` | `/bn ui` |

### 🔐 **Permissions:**
- `banknote.use` - Create and redeem banknotes (default: true)
- `banknote.admin` - Administrative access (default: op)

## 📖 **How to Use**

### **Creating Banknotes:**
1. **Command Method**: `/banknote 1000` - Creates $1000 banknote
2. **GUI Method**: `/banknote ui` - Interactive form (requires FormAPI)
3. **Quick Amounts**: Predefined buttons for common values

### **Redeeming Banknotes:**
1. **Right-click** the banknote item while holding it
2. Money is instantly added to your balance
3. Item is consumed and cannot be duplicated

### **Supported Items:**
Use any Minecraft item via configuration:
```yaml
banknote:
  item:
    type: "paper"        # Default
    type: "diamond"      # Premium look
    type: "nether_star"  # Ultra rare
    type: "emerald"      # Classic currency
    # ... and 500+ more items!
```

## ⚙️ **Configuration**

```yaml
# Enhanced Configuration v2.0.0
banknote:
  item:
    type: "paper"
    name: "§r§l§6${amount} §aBAN§fK §aNOTE"
    lore:
      - "§r§7Right-click to redeem"
      - "§r§7Value: §a${amount}"
  
  limits:
    minimum: 1
    maximum: 1000000
    cooldown: 1

ui:
  enabled: true
  title: "§l§aBankNote Manager"
  quick_amounts: [100, 500, 1000, 5000, 10000]

economy:
  preferred: "BedrockEconomy"
  auto_detect: true

security:
  anti_duplication: true
  nbt_validation: true
  block_interaction_filter: true
```

## 🔌 **Economy Plugin Compatibility**

### BedrockEconomy (Recommended)
```yaml
# Modern async economy system
- Better performance
- Async operations
- Active development
- Latest PocketMine-MP support
```

### EconomyAPI (Legacy Support)
```yaml
# Classic economy plugin
- Backwards compatibility
- Synchronous operations
- Stable and tested
- Wide plugin support
```

## 📊 **Version Comparison**

| Feature | v1.0.0 | v2.0.0 |
|---------|--------|--------|
| Economy Support | libEco only | BedrockEconomy + EconomyAPI |
| Item Support | Paper only | Any Minecraft item |
| Security | Basic | Advanced anti-duplication |
| UI | FormAPI only | FormAPI + fallback |
| Permissions | Non-standard | Professional system |
| Performance | Standard | Optimized with async |
| Poggit Compliance | Partial | Full compliance |

## 📝 **Changelog**

### **v2.0.0** (Major Update)
#### 🆕 **New Features:**
- BedrockEconomy integration with async support
- StringToItemParser for universal item support
- Advanced anti-duplication security system
- Professional permission system (`banknote.use`)
- Enhanced FormAPI integration with fallback UI
- Comprehensive configuration system
- Poggit CI compliance and quality checks

#### 🔄 **Changes:**
- **BREAKING**: Replaced libEco with BedrockEconomy
- **BREAKING**: Updated permission names for standardization
- Improved error handling and user feedback
- Cleaner code structure (removed unnecessary logging)
- Updated plugin.yml for modern standards
- Enhanced .poggit.yml configuration

#### 🐛 **Bug Fixes:**
- Fixed duplication exploits and security vulnerabilities
- Resolved interaction conflicts with storage blocks
- Improved NBT validation and item integrity checks
- Enhanced cooldown system reliability

#### 🎯 **Performance:**
- Async economy operations for better server performance
- Optimized item validation and processing
- Reduced memory footprint and CPU usage
- Faster plugin initialization and loading

### **v1.0.0** (Initial Release)
- Basic banknote functionality
- libEco integration
- FormAPI support
- Simple configuration

## 👨‍💻 **Author & Support**

**Biswajit** - Plugin Developer
- GitHub: [@GabBiswajit](https://github.com/GabBiswajit)
- Repository: [BankNote](https://github.com/GabBiswajit/BankNote)

### 🆘 **Support:**
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/GabBiswajit/BankNote/issues)
- 💡 **Feature Requests**: [GitHub Discussions](https://github.com/GabBiswajit/BankNote/discussions)
- ⭐ **Show Support**: Star the repository

---

**🎉 Thank you for using BankNote v2.0.0!**  
*Making secure economy transactions simple and reliable for the PocketMine-MP community.*
