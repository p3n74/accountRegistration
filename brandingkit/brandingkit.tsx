"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Copy, Download, Palette, Type, Layout, ImageIcon } from "lucide-react"
import { useState } from "react"

const colors = [
  { name: "Primary Blue", hex: "#4A90E2", rgb: "74, 144, 226", usage: "Primary brand color, buttons, links" },
  { name: "Dark Blue", hex: "#2E5BBA", rgb: "46, 91, 186", usage: "Headers, emphasis, hover states" },
  { name: "White", hex: "#FFFFFF", rgb: "255, 255, 255", usage: "Backgrounds, text on dark surfaces" },
  { name: "Black", hex: "#1A1A1A", rgb: "26, 26, 26", usage: "Body text, dark backgrounds" },
  { name: "Light Gray", hex: "#F5F5F5", rgb: "245, 245, 245", usage: "Subtle backgrounds, dividers" },
  { name: "Medium Gray", hex: "#9CA3AF", rgb: "156, 163, 175", usage: "Secondary text, placeholders" },
]

const logoVariations = [
  {
    name: "Primary Logo",
    bg: "bg-white",
    textColor: "text-blue-600",
    description: "Use on light backgrounds",
    logoVariant: "blue",
  },
  {
    name: "White Logo",
    bg: "bg-slate-800",
    textColor: "text-white",
    description: "Use on dark backgrounds",
    logoVariant: "white",
  },
  {
    name: "Dark Logo",
    bg: "bg-gray-100",
    textColor: "text-slate-900",
    description: "Use on light colored backgrounds",
    logoVariant: "dark",
  },
]

const typography = [
  { name: "Headings", font: "font-bold", size: "text-4xl", example: "CISCO Brand Guidelines" },
  {
    name: "Subheadings",
    font: "font-semibold",
    size: "text-2xl",
    example: "Computer and Information Sciences Council",
  },
  {
    name: "Body Text",
    font: "font-normal",
    size: "text-base",
    example: "This is the standard body text used throughout our communications.",
  },
  {
    name: "Small Text",
    font: "font-normal",
    size: "text-sm",
    example: "Used for captions, footnotes, and secondary information.",
  },
]

export default function CISCOBrandingKit() {
  const [copiedColor, setCopiedColor] = useState<string | null>(null)

  const copyToClipboard = (text: string, type: string) => {
    navigator.clipboard.writeText(text)
    setCopiedColor(type)
    setTimeout(() => setCopiedColor(null), 2000)
  }

  const LogoComponent = ({
    variant,
    className = "",
    size = "w-16 h-16",
  }: { variant: string; className?: string; size?: string }) => {
    const logoSrc = variant === "white" ? "/logo-white.png" : variant === "dark" ? "/logo-dark.png" : "/logo-blue.png"

    return (
      <div className={`flex items-center gap-4 ${className}`}>
        <img src={logoSrc || "/placeholder.svg"} alt="CISCO Logo" className={`${size} object-contain`} />
        <div className="text-4xl font-bold tracking-tight">CISCO</div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto space-y-8">
        {/* Header */}
        <div className="text-center space-y-4">
          <h1 className="text-5xl font-bold text-slate-900">CISCO Brand Kit</h1>
          <p className="text-xl text-gray-600">Computer and Information Sciences Council</p>
          <p className="text-gray-500 max-w-2xl mx-auto">
            Complete branding guidelines and assets for consistent visual identity across all communications and
            materials.
          </p>
        </div>

        {/* Color Palette */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Palette className="w-5 h-5" />
              Color Palette
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {colors.map((color) => (
                <div key={color.name} className="space-y-3">
                  <div className="w-full h-24 rounded-lg border shadow-sm" style={{ backgroundColor: color.hex }}></div>
                  <div className="space-y-2">
                    <h3 className="font-semibold text-gray-900">{color.name}</h3>
                    <div className="space-y-1 text-sm">
                      <div className="flex items-center justify-between">
                        <span className="text-gray-600">HEX:</span>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => copyToClipboard(color.hex, color.name + "-hex")}
                          className="h-6 px-2 text-xs"
                        >
                          {copiedColor === color.name + "-hex" ? "Copied!" : color.hex}
                          <Copy className="w-3 h-3 ml-1" />
                        </Button>
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-gray-600">RGB:</span>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => copyToClipboard(color.rgb, color.name + "-rgb")}
                          className="h-6 px-2 text-xs"
                        >
                          {copiedColor === color.name + "-rgb" ? "Copied!" : color.rgb}
                          <Copy className="w-3 h-3 ml-1" />
                        </Button>
                      </div>
                    </div>
                    <p className="text-xs text-gray-500">{color.usage}</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Logo Variations */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <ImageIcon className="w-5 h-5" />
              Logo Variations
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {logoVariations.map((logo, index) => (
                <div key={logo.name} className="space-y-4">
                  <div className={`${logo.bg} p-8 rounded-lg border flex items-center justify-center min-h-32`}>
                    <div className="text-center space-y-2">
                      <LogoComponent variant={logo.logoVariant} className={logo.textColor} />
                      <div className={`text-sm ${logo.textColor} opacity-75`}>
                        Computer and Information Sciences Council
                      </div>
                    </div>
                  </div>
                  <div className="text-center space-y-1">
                    <h3 className="font-semibold">{logo.name}</h3>
                    <p className="text-sm text-gray-600">{logo.description}</p>
                    <Button variant="outline" size="sm" className="mt-2">
                      <Download className="w-4 h-4 mr-2" />
                      Download
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Typography */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Type className="w-5 h-5" />
              Typography
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {typography.map((type) => (
                <div key={type.name} className="border-b border-gray-200 pb-6 last:border-b-0">
                  <div className="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div className="lg:w-1/4">
                      <h3 className="font-semibold text-gray-900">{type.name}</h3>
                      <p className="text-sm text-gray-600">
                        {type.font} {type.size}
                      </p>
                    </div>
                    <div className="lg:w-3/4">
                      <div className={`${type.font} ${type.size} text-gray-900`}>{type.example}</div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Usage Examples */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Layout className="w-5 h-5" />
              Usage Examples
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* Business Card Example */}
              <div className="space-y-3">
                <h3 className="font-semibold">Business Card</h3>
                <div className="bg-white p-6 rounded-lg border shadow-sm aspect-[3.5/2] flex flex-col justify-between">
                  <LogoComponent variant="blue" className="text-blue-600" size="w-12 h-12" />
                  <div className="space-y-1 text-sm">
                    <div className="font-semibold text-gray-900">John Doe</div>
                    <div className="text-gray-600">Director</div>
                    <div className="text-gray-500">john.doe@cisco.edu</div>
                  </div>
                </div>
              </div>

              {/* Letterhead Example */}
              <div className="space-y-3">
                <h3 className="font-semibold">Letterhead</h3>
                <div className="bg-white p-6 rounded-lg border shadow-sm">
                  <div className="border-b border-gray-200 pb-4 mb-4">
                    <LogoComponent variant="blue" className="text-blue-600" size="w-14 h-14" />
                    <div className="text-sm text-gray-600 mt-2">Computer and Information Sciences Council</div>
                  </div>
                  <div className="space-y-2 text-sm text-gray-400">
                    <div>Dear Colleague,</div>
                    <div>Lorem ipsum dolor sit amet...</div>
                  </div>
                </div>
              </div>

              {/* Website Header Example */}
              <div className="space-y-3">
                <h3 className="font-semibold">Website Header</h3>
                <div className="bg-blue-600 p-4 rounded-lg text-white">
                  <div className="flex items-center justify-between">
                    <LogoComponent variant="white" className="text-white" size="w-12 h-12" />
                    <nav className="hidden md:flex space-x-6 text-sm">
                      <a href="#" className="hover:text-blue-200">
                        About
                      </a>
                      <a href="#" className="hover:text-blue-200">
                        Programs
                      </a>
                      <a href="#" className="hover:text-blue-200">
                        Contact
                      </a>
                    </nav>
                  </div>
                </div>
              </div>

              {/* Social Media Example */}
              <div className="space-y-3">
                <h3 className="font-semibold">Social Media Post</h3>
                <div className="bg-gradient-to-br from-blue-500 to-blue-700 p-6 rounded-lg text-white aspect-square flex flex-col justify-center items-center text-center">
                  <LogoComponent variant="white" className="text-white mb-4" size="w-20 h-20" />
                  <div className="text-sm opacity-90">Advancing Computer Science Education</div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Brand Guidelines */}
        <Card>
          <CardHeader>
            <CardTitle>Brand Guidelines</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <h3 className="font-semibold text-green-700">Do's</h3>
                <ul className="space-y-2 text-sm">
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-green-500 mt-2 flex-shrink-0"></div>
                    Use the primary blue color for main brand elements
                  </li>
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-green-500 mt-2 flex-shrink-0"></div>
                    Maintain clear space around the logo equal to the height of the 'O'
                  </li>
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-green-500 mt-2 flex-shrink-0"></div>
                    Use approved color combinations for accessibility
                  </li>
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-green-500 mt-2 flex-shrink-0"></div>
                    Scale the logo proportionally
                  </li>
                </ul>
              </div>
              <div className="space-y-4">
                <h3 className="font-semibold text-red-700">Don'ts</h3>
                <ul className="space-y-2 text-sm">
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-red-500 mt-2 flex-shrink-0"></div>
                    Don't use colors outside the approved palette
                  </li>
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-red-500 mt-2 flex-shrink-0"></div>
                    Don't stretch or distort the logo
                  </li>
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-red-500 mt-2 flex-shrink-0"></div>
                    Don't place the logo on busy backgrounds
                  </li>
                  <li className="flex items-start gap-2">
                    <div className="w-2 h-2 rounded-full bg-red-500 mt-2 flex-shrink-0"></div>
                    Don't use low contrast color combinations
                  </li>
                </ul>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
