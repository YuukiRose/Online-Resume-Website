-- SQL INSERT statements for portfolio_works table
-- Generated from HTML works data

INSERT INTO portfolio_works (
    title, 
    category, 
    description, 
    image_path, 
    gallery_image_path, 
    project_url, 
    sort_order
) VALUES
(
    'OSDEPLOY - OS Deployment Suite',
    'PowerShell Deployment',
    'Collection of PowerShell scripts for Windows Autopilot provisioning and deployment automation, developed from hands-on experience at SHI International. Features device registration automation, profile configuration management, bulk deployment utilities, and compliance reporting tools. All scripts include comprehensive error handling, logging, and documentation for enterprise environments.',
    'images/portfolio/PS.PNG',
    'images/portfolio/gallery/code.jpg',
    'https://github.com/YuukiRose/OSDEPLOY',
    1
),
(
    'PEDeploy - Windows PE Toolkit',
    'WinPE Deployment',
    'Comprehensive Windows Preinstallation Environment (WinPE) deployment toolkit written entirely in PowerShell. Features modular architecture with Assets, Modules, and Scripts directories for enterprise OS deployment automation. Released under GPL-2.0 license for open-source community collaboration and enterprise deployment standardization.',
    'images/portfolio/PS.PNG',
    'images/portfolio/gallery/code.jpg',
    'https://github.com/YuukiRose/PEDeploy',
    2
),
(
    'OSDEPLOYv2 - Advanced OS Tools',
    'Enterprise Deployment',
    'Enhanced version of the OSDEPLOY toolkit featuring Active Directory login scripts and improved WinPE functionality. Built entirely in PowerShell for enterprise-grade operating system deployment automation. Includes ADLoginScript and WINPE directories with advanced deployment capabilities. Released under GPL-3.0 license for maximum community collaboration.',
    'images/portfolio/PS.PNG',
    'images/portfolio/gallery/code.jpg',
    'https://github.com/YuukiRose/OSDEPLOYv2',
    3
);
