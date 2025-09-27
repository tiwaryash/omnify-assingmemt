import * as React from "react"
import { cn } from "@/lib/utils"

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link"
  size?: "default" | "sm" | "lg" | "icon"
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = "default", size = "default", ...props }, ref) => {
    return (
      <button
        className={cn(
          "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
          {
            "bg-black text-white hover:bg-gray-800 hover:text-white": variant === "default",
            "bg-red-600 text-white hover:bg-red-700 hover:text-white": variant === "destructive",
            "border border-gray-300 bg-white text-gray-900 hover:bg-gray-50 hover:text-gray-900": variant === "outline",
            "bg-gray-200 text-gray-900 hover:bg-gray-300 hover:text-gray-900": variant === "secondary",
            "hover:bg-gray-100 text-gray-900 hover:text-gray-900": variant === "ghost",
            "text-blue-600 underline-offset-4 hover:underline hover:text-blue-600": variant === "link",
          },
          {
            "h-10 px-4 py-2": size === "default",
            "h-9 rounded-md px-3": size === "sm",
            "h-11 rounded-md px-8": size === "lg",
            "h-10 w-10": size === "icon",
          },
          className
        )}
        ref={ref}
        {...props}
      />
    )
  }
)
Button.displayName = "Button"

export { Button }
